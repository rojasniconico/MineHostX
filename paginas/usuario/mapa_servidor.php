<?php
// mapa_servidor.php
// INSTRUCCIÓN: este fichero se incluye desde ver_servidores.php después de haber obtenido
// $server (mysqli row assoc) y $id (server id).
// Ejemplo:
//   $id = intval($_GET['id']);
//   $server = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$id AND user_id=$user_id"));
//   include_once "mapa_servidor.php";

if (!isset($server) || !isset($id)) {
    echo "<!-- mapa_servidor: requiere \$server y \$id -->";
    return;
}

// Datos básicos (seguridad)
$server_name = htmlspecialchars($server['name']);
$server_ip = htmlspecialchars($server['ip'] ?? 'n/a');
$server_port = htmlspecialchars($server['port'] ?? '25565');
$server_ram_gb = intval($server['ram_gb'] ?? 2);

// Contadores reales (si existen carpetas)
$mods_count = 0;
$plugins_count = 0;
$world_size_mb = null;
$last_backup = "No disponible";

$basePath = __DIR__ . "/../../servidores/server_$id";

// contar mods
if (is_dir($basePath . "/mods")) {
    $files = scandir($basePath . "/mods");
    foreach ($files as $f) if ($f !== '.' && $f !== '..') $mods_count++;
}

// contar plugins
if (is_dir($basePath . "/plugins")) {
    $files = scandir($basePath . "/plugins");
    foreach ($files as $f) if ($f !== '.' && $f !== '..') $plugins_count++;
}

// tamaño del mundo (simulado si no hay)
$worldPath = $basePath . "/world";
if (is_dir($worldPath)) {
    $size = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($worldPath, FilesystemIterator::SKIP_DOTS));
    foreach($it as $file) { $size += $file->getSize(); }
    $world_size_mb = round($size / 1024 / 1024, 1);
} else {
    $world_size_mb = rand(50, 150); // valor simulado en MB
}

// último backup (desde BD si existe)
if (isset($conn)) {
    $res = mysqli_query($conn, "SELECT created_at FROM backups WHERE server_id=$id ORDER BY created_at DESC LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $last_backup = $row['created_at'];
    }
}

// estadísticas simuladas (para tooltip)
$ram_used_mb = rand(128, $server_ram_gb * 1024);
$ram_total_mb = $server_ram_gb * 1024;
$ram_pct = round(($ram_used_mb / max(1,$ram_total_mb)) * 100, 1);

$cpu_pct = rand(5, 80);

// helper para plural
function plural($n, $sing, $plu){ return ($n==1) ? $sing : $plu; }
?>

<!-- ===== MAPA INTERACTIVO estilo Minecraft (bloques) ===== -->
<style>
/* contenedor del mapa */
.mh-map {
  max-width:900px;
  margin:20px auto;
  display:grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap:12px;
  font-family: "Segoe UI", Arial, sans-serif;
  user-select:none;
}

/* bloque estilo "pixel" */
.mh-block {
  background: linear-gradient(180deg, #2b2b2b 0%, #1b1b1b 100%);
  border:4px solid #0e0e0e;
  padding:14px;
  border-radius:6px;
  box-shadow: 0 6px 0 rgba(0,0,0,0.6), inset 0 -6px 0 rgba(0,0,0,0.35);
  position:relative;
  cursor:pointer;
  transition: transform .12s ease, box-shadow .12s ease;
  text-align:center;
}

/* hover "ilumina" estilo Minecraft */
.mh-block:hover {
  transform: translateY(-6px) scale(1.03);
  box-shadow: 0 12px 0 rgba(0,0,0,0.65), 0 6px 18px rgba(0,0,0,0.6);
}

/* Icono cuadrado pixel */
.mh-icon {
  width:72px;
  height:72px;
  margin:0 auto 8px;
  border-radius:6px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:34px;
  box-shadow: inset 0 -8px rgba(0,0,0,0.25);
}

/* colores tipo Minecraft */
.c-ram { background: linear-gradient(#7cc576,#5aa65a); color:#063; }
.c-cpu { background: linear-gradient(#f2c96d,#e5b14a); color:#442a00; }
.c-mods { background: linear-gradient(#a078c8,#7b4aa8); color:#fff; }
.c-plugins { background: linear-gradient(#6bc1e6,#3fa5d6); color:#01273a; }
.c-world { background: linear-gradient(#d9a563,#b17b3a); color:#2b1506; }
.c-backup { background: linear-gradient(#f7e08b,#e6c75a); color:#3b2b00; }
.c-logs { background: linear-gradient(#ff0000,#ff6540); color:#3b2b00; }
.c-consola { background: linear-gradient(#F5F5F5,#F5F5F5); color:#3b2b00; }
.c-tareas { background: linear-gradient(#254117,#254117); color:#3b2b00; }


/* título y valores */
.mh-title { font-weight:700; color:#fff; margin-bottom:6px; font-size:15px; text-shadow:0 1px 0 rgba(0,0,0,0.6); }
.mh-sub { color:#ddd; font-size:13px; }

/* tooltip */
.mh-tooltip {
  position:fixed;
  pointer-events:none;
  background:rgba(18,18,18,0.95);
  color:#fff;
  padding:10px 12px;
  border-radius:8px;
  box-shadow:0 6px 20px rgba(0,0,0,0.6);
  font-size:13px;
  z-index:9999;
  display:none;
  max-width:260px;
  line-height:1.25em;
  border:2px solid rgba(255,255,255,0.03);
}

/* aspecto responsive */
@media (max-width:820px){
  .mh-map { grid-template-columns: 1fr 1fr; }
  .mh-icon { width:56px; height:56px; font-size:28px; }
}
@media (max-width:480px){
  .mh-map { grid-template-columns: 1fr; }
}
</style>

<div class="mh-map" id="mh-map">

  <!-- CPU -->
  <div class="mh-block" data-action="estadisticas" data-title="CPU / Rendimiento"
       data-desc="Carga CPU: <?php echo $cpu_pct; ?>%. Haz click para ver métricas más detalladas.">
    <div class="mh-icon c-cpu">⚙️</div>
    <div class="mh-title">CPU</div>
    <div class="mh-sub">Uso <?php echo $cpu_pct; ?>%</div>
  </div>

  <!-- RAM -->
  <div class="mh-block" data-action="estadisticas" data-title="RAM"
       data-desc="RAM usada: <?php echo $ram_used_mb . ' MB'; ?> / <?php echo $ram_total_mb . ' MB'; ?> (<?php echo $ram_pct; ?>%). Haz click para recomendaciones.">
    <div class="mh-icon c-ram">💾</div>
    <div class="mh-title">RAM</div>
    <div class="mh-sub"><?php echo $ram_used_mb . ' / ' . $ram_total_mb; ?> MB</div>
  </div>

  <!-- Mundo -->
  <div class="mh-block" data-action="mundo" data-title="Mundo" data-desc="Tamaño aproximado del mundo: <?php echo $world_size_mb;?> MB. Aprende a optimizar tu mundo.">
    <div class="mh-icon c-world">🌍</div>
    <div class="mh-title">Mundo</div>
    <div class="mh-sub"><?php echo $world_size_mb;?> MB</div>
  </div>

  <!-- Mods -->
  <div class="mh-block" data-action="mods" data-title="Mods" data-desc="Mods instalados: <?php echo $mods_count; ?>. Haz click para gestionar.">
    <div class="mh-icon c-mods">🧩</div>
    <div class="mh-title">Mods</div>
    <div class="mh-sub"><?php echo $mods_count . ' ' . plural($mods_count,'mod','mods'); ?></div>
  </div>

  <!-- Plugins -->
  <div class="mh-block" data-action="plugins" data-title="Plugins" data-desc="Plugins instalados: <?php echo $plugins_count; ?>. Haz click para gestionar.">
    <div class="mh-icon c-plugins">🔌</div>
    <div class="mh-title">Plugins</div>
    <div class="mh-sub"><?php echo $plugins_count . ' ' . plural($plugins_count,'plugin','plugins'); ?></div>
  </div>

  <!-- Backups -->
  <div class="mh-block" data-action="backups" data-title="Backups" data-desc="Último backup: <?php echo htmlspecialchars($last_backup); ?>. Haz click para ver o crear backups.">
    <div class="mh-icon c-backup">📦</div>
    <div class="mh-title">Backups</div>
    <div class="mh-sub">Último: <?php echo htmlspecialchars($last_backup); ?></div>
  </div>

  <!-- Logs -->
  <div class="mh-block" data-action="logs" data-title="logs" . Haz click para ver lso logs.">
    <div class="mh-icon c-logs">📜</div>
    <div class="mh-title">Logs</div>
  </div>

  <!-- Consola -->
  <div class="mh-block" data-action="consola" data-title="consola" . Haz click para ver y usar la consola.">
    <div class="mh-icon c-consola">🖥</div>
    <div class="mh-title">Consola</div>
  </div>

  <!-- Tareas Programadas -->
  <div class="mh-block" data-action="tareas" data-title="tareas" . Haz click para ver las tareas programadas.">
    <div class="mh-icon c-tareas">🕒</div>
    <div class="mh-title">Tareas programadas</div>
  </div>



</div>

<!-- tooltip -->
<div id="mh-tooltip" class="mh-tooltip"></div>

<script>
// tooltip behaviour
const tooltip = document.getElementById('mh-tooltip');
const blocks = document.querySelectorAll('#mh-map .mh-block');

blocks.forEach(b => {
  b.addEventListener('mousemove', (e) => {
    const title = b.getAttribute('data-title') || '';
    const desc = b.getAttribute('data-desc') || '';
    tooltip.innerHTML = "<strong style='color:#4FC3F7'>" + title + "</strong><div style='margin-top:6px;color:#ddd;font-size:13px;'>" + desc + "</div>";
    tooltip.style.display = 'block';
    // position tooltip near cursor but inside viewport
    let left = e.clientX + 14;
    let top = e.clientY + 14;
    if (left + tooltip.offsetWidth > window.innerWidth) left = e.clientX - tooltip.offsetWidth - 20;
    if (top + tooltip.offsetHeight > window.innerHeight) top = e.clientY - tooltip.offsetHeight - 20;
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
  });

  b.addEventListener('mouseleave', () => {
    tooltip.style.display = 'none';
  });

  b.addEventListener('click', () => {
    const action = b.getAttribute('data-action');
    // redirecciones: ajusta las rutas si tu estructura es distinta
    if (action === 'mods') {
      location.href = 'mods.php?id=<?php echo $id; ?>';
    } else if (action === 'plugins') {
      location.href = 'plugins.php?id=<?php echo $id; ?>';
    } else if (action === 'backups') {
      location.href = 'backups.php?id=<?php echo $id; ?>';
    } else if (action === 'logs') {
      location.href = 'server_logs.php?id=<?php echo $id; ?>';
    } else if (action === 'consola') {
      location.href = 'consola.php?id=<?php echo $id; ?>';
    } else if (action === 'tareas') {
      location.href = 'tareas.php?id=<?php echo $id; ?>';
    } else if (action === 'estadisticas') {
      location.href = 'estadisticas.php?id=<?php echo $id; ?>';
    } else if (action === 'mundo') {
      location.href = 'mundo.php?id=<?php echo $id; ?>';
    } else {
      // por defecto volver al panel del servidor
      location.href = 'ver_servidores.php?id=<?php echo $id; ?>';
    }
  });
});
</script>

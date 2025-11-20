<?php
// backups.php (MineHostX - estilo B, funcionalidades avanzadas)
session_start();
require_once "../../db.php";
require_once "../../comun/puntos.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../../autenticacion/login.php"); exit(); }

$server_id = intval($_GET['id'] ?? 0);
$user_id   = intval( $_SESSION['user_id']);

// --- verificar servidor y permisos
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM servers WHERE id=$server_id"));
if (!$sv) die("❌ Servidor no existe.");
if ($sv['user_id'] != $user_id && $_SESSION['role']!=='admin') die("⚠ No autorizado.");

// --- rutas
$base = realpath("../../servidores/server_$server_id") . DIRECTORY_SEPARATOR;
$backupsDir = $base . "backups" . DIRECTORY_SEPARATOR;
if (!is_dir($backupsDir)) mkdir($backupsDir, 0777, true);

// --- crear tablas auxiliares si no existen
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS backup_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  server_id INT NOT NULL,
  type ENUM('daily','weekly','on_start') NOT NULL,
  enabled TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS backup_exports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  server_id INT NOT NULL,
  file_path VARCHAR(255),
  provider VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS backup_restore_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  server_id INT NOT NULL,
  file_path VARCHAR(255),
  restored_by INT,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// --- acciones POST (create, delete, restore, export, schedule create/toggle, run_schedule)
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // crear backup manual
    if (isset($_POST['create_backup'])) {
        $fname = "backup_" . date('Ymd_His') . ".zip";
        $zipPath = $backupsDir . $fname;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->isDir()) continue;
                $filePath = $file->getRealPath();
                $rel = substr($filePath, strlen($base));
                if (strpos($rel, "backups" . DIRECTORY_SEPARATOR) === 0) continue;
                $zip->addFile($filePath, $rel);
            }
            $zip->close();
            $fname_db = mysqli_real_escape_string($conn, $fname);
            mysqli_query($conn, "INSERT INTO backups (server_id, file_path) VALUES ($server_id, '$fname_db')");

addPoints($conn, $user_id, 5, "Backup realizado");
giveAchievement($conn, $user_id, "BACKUP_GUARDIAN");


            $msg = "✅ Backup creado: $fname";
        } else {
            $msg = "❌ Error creando el ZIP (activa la extensión zip en PHP).";
        }
    }

    // eliminar backup
    if (isset($_POST['delete']) && isset($_POST['file'])) {
        $f = basename($_POST['file']);
        $path = $backupsDir . $f;
        if (file_exists($path)) unlink($path);
        $f_db = mysqli_real_escape_string($conn, $f);
        mysqli_query($conn, "DELETE FROM backups WHERE server_id=$server_id AND file_path='$f_db'");
        $msg = "🗑 Backup eliminado: $f";
    }

    // restaurar (simulado: registrar evento)
    if (isset($_POST['restore']) && isset($_POST['file'])) {
        $f = basename($_POST['file']);
        // registrar restauración (simulada)
        $note = mysqli_real_escape_string($conn, $_POST['restore_note'] ?? 'Restauración iniciada desde panel');
        mysqli_query($conn, "INSERT INTO backup_restore_logs (server_id, file_path, restored_by, note) VALUES ($server_id,'$f',$user_id,'$note')");
        $msg = "♻ Restauración registrada para: $f (simulada).";
    }

    // exportar (simulado)
    if (isset($_POST['export']) && isset($_POST['file'])) {
        $f = basename($_POST['file']);
        $provider = mysqli_real_escape_string($conn, $_POST['provider'] ?? 'gdrive_sim');
        mysqli_query($conn, "INSERT INTO backup_exports (server_id, file_path, provider) VALUES ($server_id,'$f','$provider')");
        $msg = "📤 Backup exportado (simulado) a $provider: $f";
    }

    // crear schedule
    if (isset($_POST['create_schedule']) && isset($_POST['schedule_type'])) {
        $type = mysqli_real_escape_string($conn, $_POST['schedule_type']);
        mysqli_query($conn, "INSERT INTO backup_schedules (server_id,type) VALUES ($server_id,'$type')");
        $msg = "⏱ Programación creada: $type";
    }

    // toggle schedule
    if (isset($_POST['toggle_schedule']) && isset($_POST['sid'])) {
        $sid = intval($_POST['sid']);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM backup_schedules WHERE id=$sid AND server_id=$server_id"));
        if ($row) {
            $new = $row['enabled'] ? 0 : 1;
            mysqli_query($conn, "UPDATE backup_schedules SET enabled=$new WHERE id=$sid");
            $msg = $new ? "✅ Programación activada" : "⛔ Programación desactivada";
        }
    }

    // run schedule (simulado): crea un backup
    if (isset($_POST['run_schedule']) && isset($_POST['sid'])) {
        $sid = intval($_POST['sid']);
        // simple: crear backup ahora
        $_POST['create_backup'] = 1; // reusar lógica de creación
        // reenviar POST: we'll create backup by duplicating above logic manually to ensure immediate run
        $fname = "backup_" . date('Ymd_His') . ".zip";
        $zipPath = $backupsDir . $fname;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->isDir()) continue;
                $filePath = $file->getRealPath();
                $rel = substr($filePath, strlen($base));
                if (strpos($rel, "backups" . DIRECTORY_SEPARATOR) === 0) continue;
                $zip->addFile($filePath, $rel);
            }
            $zip->close();
            $fname_db = mysqli_real_escape_string($conn, $fname);
            mysqli_query($conn, "INSERT INTO backups (server_id, file_path) VALUES ($server_id, '$fname_db')");
            $msg = "⏱ Programación ejecutada: backup creado $fname";
        } else {
            $msg = "❌ Error creando backup por schedule.";
        }
    }
}

// --- obtener backups desde BD y enriquecer con información de archivos
$bs_raw = mysqli_query($conn, "SELECT * FROM backups WHERE server_id=$server_id ORDER BY created_at DESC");
$backups = [];
while ($b = mysqli_fetch_assoc($bs_raw)) {
    $file = $b['file_path'];
    $full = $backupsDir . $file;
    $size = file_exists($full) ? filesize($full) : 0;
    $md5  = file_exists($full) ? md5_file($full) : '';
    $files_list = [];
    // intentar listar contenido del zip (si existe)
    if (file_exists($full)) {
        $za = new ZipArchive();
        if ($za->open($full) === TRUE) {
            for ($i=0;$i<$za->numFiles;$i++) {
                $files_list[] = $za->getNameIndex($i);
            }
            $za->close();
        }
    }
    // categorizar: manual (nombre contiene "backup_"), automatico (contains "auto"), critico (contains "critical")
    $cat = 'manual';
    $lname = strtolower($file);
    if (strpos($lname,'auto')!==false) $cat='automatico';
    if (strpos($lname,'crit')!==false) $cat='critico';

    $backups[] = [
        'db' => $b,
        'file' => $file,
        'full' => $full,
        'size' => $size,
        'md5' => $md5,
        'files' => $files_list,
        'category' => $cat
    ];
}

// schedules, exports, restore logs
$schedules = mysqli_query($conn, "SELECT * FROM backup_schedules WHERE server_id=$server_id ORDER BY created_at DESC");
$exports = mysqli_query($conn, "SELECT * FROM backup_exports WHERE server_id=$server_id ORDER BY created_at DESC");
$restores = mysqli_query($conn, "SELECT * FROM backup_restore_logs WHERE server_id=$server_id ORDER BY created_at DESC");

function human_filesize($bytes, $decimals = 2) {
    $sz = ['B','KB','MB','GB','TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor == 0) return $bytes . ' B';
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sz[$factor];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Backups — <?=htmlspecialchars($sv['name'])?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>

/* Footer */
footer {
  background: #0d0d0d;
  color: #aaa;
  text-align: center;
  padding: 20px;
  font-size: 0.9em;
}
footer a {
  color: #4FC3F7;
  text-decoration: none;
}
/* Estilo B - MineHostX */
:root {
  --accent:#4FC3F7;
  --bg:#121212;
  --card:#1E1E1E;
  --muted:#aaa;
}
body { background:var(--bg); color:#fff; font-family:'Segoe UI',sans-serif; margin:0; padding:0; }
.topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 28px; border-bottom:1px solid rgba(255,255,255,0.03); }
.title { font-size:20px; color:var(--accent); font-weight:700; display:flex; align-items:center; gap:10px; }
.controls { display:flex; gap:10px; align-items:center; }

/* search & sort */
.search { background:#0f0f0f; padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); color:#fff; }
.select { background:#0f0f0f; padding:8px 10px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); color:#fff; }

/* grid */
.container { max-width:1100px; margin:28px auto; padding:0 18px; }
.grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(260px,1fr)); gap:18px; margin-top:22px; }

/* card */
.card { background:var(--card); border-radius:12px; padding:16px; box-shadow:0 8px 24px rgba(0,0,0,0.5); transition:transform .15s ease, box-shadow .15s ease; }
.card:hover { transform:translateY(-6px); box-shadow:0 20px 36px rgba(0,0,0,0.6); }
.icon { font-size:36px; display:inline-block; padding:8px; border-radius:10px; background:linear-gradient(180deg,#2a2a2a,#151515); margin-bottom:10px; }
.name { font-weight:700; color:var(--accent); margin-top:6px; }
.meta { color:var(--muted); font-size:13px; margin-top:6px; }
.actions { margin-top:12px; display:flex; gap:8px; flex-wrap:wrap; }
.btn { background:#111; color:#fff; padding:8px 10px; border-radius:8px; text-decoration:none; border:1px solid rgba(255,255,255,0.03); cursor:pointer; }
.btn:hover { background:var(--accent); color:#000; }
.btn-danger { background:#b33; color:#fff; }
.tag { display:inline-block; padding:6px 8px; border-radius:8px; font-size:12px; color:#000; background:var(--accent); font-weight:700; }

/* panels later */
.side { margin-top:22px; display:flex; gap:12px; align-items:center; }

/* schedule list */
.schedule-list { margin-top:18px; display:flex; gap:10px; flex-wrap:wrap; }
.schedule-card { background:#111; padding:8px 12px; border-radius:8px; color:#ddd; font-size:14px; }

/* modal */
.modal-back { position:fixed; inset:0; background:rgba(0,0,0,0.6); display:none; align-items:center; justify-content:center; z-index:9999; }
.modal { background:#0f0f0f; padding:18px; border-radius:12px; width:90%; max-width:780px; color:#fff; box-shadow:0 20px 40px rgba(0,0,0,0.7); }
.modal h3 { color:var(--accent); margin-top:0; }

/* responsive */
@media (max-width:760px) {
  .topbar { flex-direction:column; align-items:flex-start; gap:12px; }
  .controls { width:100%; justify-content:space-between; }
}

</style>
</head>
<body>

<!-- topbar -->
<div class="topbar">
  <div class="title">💾 Backups — <?=htmlspecialchars($sv['name'])?></div>
  <div class="controls">
    <form method="POST" style="display:inline;margin:0;">
      <button class="btn" name="create_backup" style="background:var(--accent);color:#000;padding:10px 14px;border-radius:10px;font-weight:800;">📦 Crear backup</button>
    </form>

    <input id="searchInput" class="search" placeholder="Buscar backup por nombre o fecha...">
    <select id="sortSelect" class="select">
      <option value="date_desc">Orden: Fecha (nuevos)</option>
      <option value="date_asc">Orden: Fecha (antiguos)</option>
      <option value="size_desc">Orden: Tamaño (mayor)</option>
      <option value="size_asc">Orden: Tamaño (menor)</option>
      <option value="name_asc">Orden: Nombre (A→Z)</option>
      <option value="name_desc">Orden: Nombre (Z→A)</option>
    </select>
  </div>
</div>

<div class="container">
  <!-- resumen -->
  <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
    <div class="side">
      <div style="background:linear-gradient(180deg,#17202a,#0f1620);padding:12px;border-radius:12px;">
        <div style="font-size:18px;font-weight:800;color:var(--accent);">Backups</div>
        <div style="color:var(--muted);">Gestiona copias manuales y programadas</div>
      </div>
    </div>

    <div class="side" style="margin-left:auto;">
      <div class="schedule-list">
        <?php while($sch = mysqli_fetch_assoc($schedules)): ?>
          <div class="schedule-card">
            <?=htmlspecialchars($sch['type'])?> — <?= $sch['enabled'] ? 'Activo' : 'Desactivado' ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="sid" value="<?=intval($sch['id'])?>">
              <button class="btn" name="toggle_schedule" style="margin-left:8px; background:#222;">Toggle</button>
            </form>
            <form method="POST" style="display:inline">
              <input type="hidden" name="sid" value="<?=intval($sch['id'])?>">
              <button class="btn" name="run_schedule" style="margin-left:4px;">Ejecutar ahora</button>
            </form>
          </div>
        <?php endwhile; ?>

        <!-- crear nuevo schedule -->
        <div class="schedule-card">
          <form method="POST" style="display:flex;gap:6px;align-items:center;">
            <select name="schedule_type" style="background:#111;color:#fff;border:none;padding:6px;border-radius:8px;">
              <option value="daily">Diario</option>
              <option value="weekly">Semanal</option>
              <option value="on_start">Al iniciar</option>
            </select>
            <button class="btn" name="create_schedule">➕ Añadir</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- filtros visuales -->
  <div style="margin-top:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <button class="btn" data-filter="all">Todos</button>
    <button class="btn" data-filter="manual">Manual</button>
    <button class="btn" data-filter="automatico">Automático</button>
    <button class="btn" data-filter="critico">Crítico</button>
    <div style="flex:1"></div>
    <a class="btn" href="ver_servidores.php?id=<?=$server_id?>">⬅ Volver</a>
  </div>

  <!-- grid de backups -->
  <div id="grid" class="grid">
    <?php foreach($backups as $b): 
        $created = htmlspecialchars($b['db']['created_at']);
        $fname = htmlspecialchars($b['file']);
        $sizeH = human_filesize($b['size']);
        $md5 = $b['md5'] ?: '—';
        $category = $b['category'];
        $filesCount = count($b['files']);
    ?>
    <div class="card" data-name="<?=htmlspecialchars(strtolower($fname))?>" data-date="<?=htmlspecialchars($created)?>" data-size="<?=intval($b['size'])?>" data-cat="<?=$category?>">
      <div class="icon">🗂️</div>
      <div class="name"><?=$fname?></div>
      <div class="meta"><?=$created?> • <?=$sizeH?> • <?= $category ?></div>
      <div style="margin-top:8px;color:var(--muted);font-size:13px;"><?= $filesCount ?> archivos contenidos</div>

      <div class="actions">
        <button class="btn" onclick="openDetails('<?=addslashes($fname)?>')">ℹ Ver</button>

        <form method="POST" style="display:inline">
          <input type="hidden" name="file" value="<?=$fname?>">
          <button class="btn" name="download_btn" formaction="download_backup.php?file=<?=urlencode($fname)?>&server=<?=$server_id?>">⬇ Descargar</button>
        </form>

        <button class="btn" onclick="confirmRestore('<?=addslashes($fname)?>')">♻ Restaurar</button>

        <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar backup?');">
          <input type="hidden" name="file" value="<?=$fname?>">
          <button class="btn btn-danger" name="delete">🗑 Eliminar</button>
        </form>

        <button class="btn" onclick="exportBackup('<?=addslashes($fname)?>')">📤 Exportar</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- detalle modal -->
  <div id="modal" class="modal-back" onclick="if(event.target.id==='modal') closeModal();">
    <div class="modal" id="modalInner">
      <h3>Detalle del backup</h3>
      <div id="modalContent"></div>
      <div style="text-align:right;margin-top:12px;">
        <button class="btn" onclick="closeModal()">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- confirmar restauracion modal -->
  <div id="confirmModal" class="modal-back">
    <div class="modal">
      <h3>Confirmar restauración</h3>
      <div id="confirmText"></div>
      <form method="POST" id="restoreForm">
        <input type="hidden" name="file" id="restoreFile">
        <div style="margin-top:8px;">
          <label style="color:var(--muted);font-size:13px;">Nota (opcional)</label><br>
          <input name="restore_note" style="width:100%;padding:8px;background:#111;border:none;border-radius:8px;color:#fff;">
        </div>
        <div style="text-align:right;margin-top:12px;">
          <button type="button" class="btn" onclick="closeConfirm()">Cancelar</button>
          <button type="submit" name="restore" class="btn" style="background:var(--accent);color:#000;">♻ Confirmar restauración</button>
        </div>
      </form>
    </div>
  </div>

  <!-- export modal -->
  <div id="exportModal" class="modal-back">
    <div class="modal">
      <h3>Exportar backup</h3>
      <div id="exportText"></div>
      <form method="POST" id="exportForm">
        <input type="hidden" name="file" id="exportFile">
        <div style="margin-top:8px;">
          <label style="color:var(--muted);font-size:13px;">Proveedor (simulado)</label><br>
          <select name="provider" style="width:100%;padding:8px;border-radius:8px;border:none;background:#111;color:#fff;">
            <option value="gdrive_sim">Google Drive (simulado)</option>
            <option value="dropbox_sim">Dropbox (simulado)</option>
          </select>
        </div>
        <div style="text-align:right;margin-top:12px;">
          <button type="button" class="btn" onclick="closeExport()">Cancelar</button>
          <button type="submit" name="export" class="btn" style="background:var(--accent);color:#000;">📤 Exportar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- actividades recientes (exports/restores) -->
  <div style="margin-top:28px;">
    <h3 style="color:var(--accent)">Actividad reciente</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:10px;">
      <?php while($e = mysqli_fetch_assoc($exports)): ?>
        <div style="background:#111;padding:10px;border-radius:8px;color:#ddd;">
          📤 <?=htmlspecialchars($e['file_path'])?> — <?=htmlspecialchars($e['provider'])?> — <?=htmlspecialchars($e['created_at'])?>
        </div>
      <?php endwhile; ?>

      <?php while($r = mysqli_fetch_assoc($restores)): ?>
        <div style="background:#111;padding:10px;border-radius:8px;color:#ddd;">
          ♻ <?=htmlspecialchars($r['file_path'])?> — <?=htmlspecialchars($r['note'])?> — <?=htmlspecialchars($r['created_at'])?>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

</div>

<script>
// Frontend interactivity: search, sort, filter, modals
const grid = document.getElementById('grid');
const cards = [...grid.querySelectorAll('.card')];

document.getElementById('searchInput').addEventListener('input', (e)=>{
  const q = e.target.value.toLowerCase();
  cards.forEach(c=>{
    const name = c.getAttribute('data-name');
    const date = c.getAttribute('data-date').toLowerCase();
    c.style.display = (name.includes(q) || date.includes(q)) ? '' : 'none';
  });
});

document.getElementById('sortSelect').addEventListener('change', (e)=>{
  const v = e.target.value;
  const sorted = cards.slice().sort((a,b)=>{
    if (v === 'date_desc') return new Date(b.dataset.date) - new Date(a.dataset.date);
    if (v === 'date_asc') return new Date(a.dataset.date) - new Date(b.dataset.date);
    if (v === 'size_desc') return parseInt(b.dataset.size) - parseInt(a.dataset.size);
    if (v === 'size_asc') return parseInt(a.dataset.size) - parseInt(b.dataset.size);
    if (v === 'name_asc') return a.dataset.name.localeCompare(b.dataset.name);
    if (v === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
    return 0;
  });
  grid.innerHTML = '';
  sorted.forEach(c=> grid.appendChild(c));
});

document.querySelectorAll('[data-filter]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const f = btn.getAttribute('data-filter');
    cards.forEach(c=>{
      if (f === 'all') c.style.display = '';
      else c.style.display = (c.dataset.cat === f) ? '' : 'none';
    });
  });
});

function openDetails(fname) {
  // buscar card by name
  const c = cards.find(x => x.dataset.name === fname.toLowerCase());
  if (!c) return;
  // construir contenido desde atributos (podemos hacer petición AJAX para más info si se desea)
  const name = c.querySelector('.name').innerText;
  const meta = c.querySelector('.meta').innerText;
  const details = `<p style="color:#ddd;"><b>${name}</b><br><span style="color:var(--muted)">${meta}</span></p>`;
  // obtener files list via AJAX (call back to this page? better to fetch a small endpoint)
  fetch('?action=details&file=' + encodeURIComponent(name))
    .then(r=>r.json())
    .then(json=>{
      let files = '';
      if (json.files && json.files.length) {
        files = '<h4 style="color:var(--accent);margin-bottom:6px">Archivos incluidos</h4><ul style="max-height:240px;overflow:auto;color:#ddd;padding-left:18px;">';
        json.files.forEach(f => files += '<li>'+f+'</li>');
        files += '</ul>';
      } else files = '<p style="color:#aaa">No se pudo leer el ZIP o está vacío.</p>';
      const extra = `<p style="color:var(--muted);font-size:13px;margin-top:8px">MD5: ${json.md5 || '—'} • Tamaño: ${json.size_human || '—'}</p>`;
      document.getElementById('modalContent').innerHTML = details + files + extra;
      document.getElementById('modal').style.display = 'flex';
    })
    .catch(()=> {
      document.getElementById('modalContent').innerHTML = details + '<p style="color:#aaa">Error al cargar detalles.</p>';
      document.getElementById('modal').style.display = 'flex';
    });
}

function closeModal(){ document.getElementById('modal').style.display='none'; }

function confirmRestore(fname) {
  document.getElementById('confirmText').innerHTML = `<p style="color:#ddd">Vas a restaurar <b>${fname}</b>. Esto reemplazará archivos del servidor (simulado). ¿Continuar?</p>`;
  document.getElementById('restoreFile').value = fname;
  document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirm(){ document.getElementById('confirmModal').style.display='none'; }

function exportBackup(fname) {
  document.getElementById('exportText').innerHTML = `<p style="color:#ddd">Exportar <b>${fname}</b> a un proveedor (simulado).</p>`;
  document.getElementById('exportFile').value = fname;
  document.getElementById('exportModal').style.display = 'flex';
}
function closeExport(){ document.getElementById('exportModal').style.display='none'; }
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

</body>
</html>


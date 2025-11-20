<?php
session_start();
require_once "../../db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🤖 Asistente de Servidores — MineHostX</title>

<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; padding-top: 60px; }
.container { width:90%; max-width: 800px; margin:auto; padding-top:30px; }
h1 { color:#4FC3F7; text-align:center; margin-bottom: 30px; }

.box {
  background:#1E1E1E;
  padding:25px;
  border-radius:12px;
  margin-bottom:20px;
  box-shadow:0 4px 12px rgba(0,0,0,0.3);
}

label { font-weight:bold; display: block; margin-top: 15px; margin-bottom: 5px; color: #ccc; }

select, input[type="number"] {
  width:100%;
  padding:12px;
  border-radius:8px;
  border:1px solid #333;
  background:#272727;
  color:#fff;
  box-sizing: border-box;
}

button {
  margin-top:30px;
  padding:12px 25px;
  background:#FF9800; /* Naranja para acción */
  color:#000;
  border:none;
  border-radius:8px;
  cursor:pointer;
  font-weight:bold;
  font-size: 1.1em;
  transition: background 0.2s;
}
button:hover { background: #FFB74D; }

.result {
  background:#191919;
  padding:30px;
  border-radius:12px;
  margin-top:30px;
  display:none;
}
.result h2 { color: #A5D6A7; margin-top: 0; }
.result p { line-height: 1.5; margin-bottom: 10px; }
.recommendation { font-weight: bold; color: #4FC3F7; }

a.btn-create {
  display:inline-block;
  margin-top:25px;
  padding:12px 20px;
  background:#4CAF50; /* Verde para crear */
  color:#fff;
  font-weight:bold;
  border-radius:8px;
  text-decoration:none;
  transition: background 0.2s;
}
a.btn-create:hover { background: #66BB6A; }

/* Footer */
footer {
  background: #0d0d0d;
  color: #aaa;
  text-align: center;
  padding: 20px;
  margin-top: 50px;
  font-size: 0.9em;
}
footer a {
  color: #4FC3F7;
  text-decoration: none;
}
</style>

<script>
function calcular() {
  let jugadores = parseInt(document.getElementById("players").value);
  let mods = document.getElementById("mods").value;
  let tipo = document.getElementById("type").value;

  // 1. Lógica de RAM base por jugadores (tasa de 0.5GB por jugador base)
  let ram = 2; // Mínimo de 2GB
  let ram_reason = "Base para estabilidad y sistema operativo del servidor.";

  if (jugadores > 2) {
    ram = Math.ceil(jugadores * 0.5); 
    ram_reason = `Calculado para ${jugadores} jugadores. Se recomienda 0.5 GB por jugador activo.`;
  }
  if (jugadores > 15) {
    ram = jugadores * 0.4; // Ligeramente menos por economía de escala
    ram_reason = `Para grandes comunidades (${jugadores} personas), se necesita un gran búfer de ${ram} GB.`;
  }
    ram = Math.max(2, Math.round(ram)); // Asegurar que sea al menos 2GB y redondeado

  // 2. Ajuste por Mods
  let software = "Vanilla";
  if (mods === "pocos") {
    ram += 1;
    software = "Fabric / Forge (Ligero)";
    ram_reason += " **( +1 GB por mods ligeros: **Los mods requieren RAM adicional para cargar texturas y objetos.)";
  }
  if (mods === "muchos") {
    ram += 3;
    software = "Forge (Pesado)";
    ram_reason += " **( +3 GB por mods pesados: **Grandes packs de mods necesitan RAM para precargar datos y texturas 3D.)";
  }

  // 3. Ajuste por Tipo de Servidor
  let plan = "Free";
  let soft_reason = "";
  
  if (tipo === "pvp") {
    software = "Paper / Purpur";
    soft_reason = "Seleccionamos **Paper/Purpur** (basado en Spigot) ya que son altamente optimizados para combate y baja latencia (PvP).";
  } else if (tipo === "creativo") {
    software = "Vanilla";
    soft_reason = "El modo creativo consume menos recursos que el modo supervivencia, manteniendo el software base.";
  } else {
    software = "Spigot / Paper";
    soft_reason = "Seleccionamos **Spigot/Paper** para optimizar el rendimiento y permitir la adición de plugins básicos.";
  }

  // 4. Asignación de Plan
  if (ram > 5) plan = "Premium";
  if (ram > 10) plan = "Enterprise";

  // 5. Versión por defecto (Podrías añadir un select para esto)
  let version = "1.20.1";

  // Mostrar resultados
  document.getElementById("r_ram").innerHTML = `<span class="recommendation">${ram} GB</span>`;
  document.getElementById("r_ram_reason").innerText = ram_reason;

  document.getElementById("r_soft").innerHTML = `<span class="recommendation">${software} (${version})</span>`;
  document.getElementById("r_soft_reason").innerText = soft_reason;
  
  document.getElementById("r_plan").innerHTML = `<span class="recommendation">${plan}</span>`;
  document.getElementById("r_plan_reason").innerText = `El plan ${plan} es necesario para cubrir los ${ram} GB de RAM recomendados y el software de alto rendimiento.`;

  // Crear enlace dinámico para la creación
  let link = `crear_servidor.php?ram=${ram}&software=${software}&version=${version}`;
  document.getElementById("btn-crear").href = link;

  document.getElementById("result").style.display = "block";
}
</script>

</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="container">

<h1>🤖 Asistente de Configuración de Servidores</h1>

<div class="box">
    <p style="color: #4FC3F7; font-style: italic;">Usa este asistente para calcular el hardware y software óptimo para tu servidor de Minecraft.</p>

    <label for="players">¿Cuántas personas jugarán activamente?</label>
    <input type="number" id="players" value="4" min="1">

    <label for="mods">¿Quieres usar mods?</label>
    <select id="mods">
        <option value="no">No, solo Vanilla (Recomendado para novatos)</option>
        <option value="pocos">Sí, pocos (1–10 mods pequeños)</option>
        <option value="muchos">Sí, muchos (Packs de mods grandes)</option>
    </select>

    <label for="type">¿Tipo de servidor?</label>
    <select id="type">
        <option value="supervivencia">Supervivencia Clásica/PVE (Jugadores vs Entorno)</option>
        <option value="pvp">PvP / Minijuegos (Alto tráfico de entidades)</option>
        <option value="creativo">Creativo / Construcción (Mundo estático)</option>
    </select>

    <button onclick="calcular()">Calcular configuración óptima</button>

</div>

<div class="result" id="result">
    <h2>✅ Configuración Optimizada</h2>

    <p><b>RAM Total Recomendada:</b> <span id="r_ram"></span></p>
    <p style="font-size: 0.9em; color: #aaa;">Motivo: <span id="r_ram_reason"></span></p>
    
    <hr style="border-top: 1px solid #333; margin: 15px 0;">

    <p><b>Software/Motor Recomendado:</b> <span id="r_soft"></span></p>
    <p style="font-size: 0.9em; color: #aaa;">Motivo: <span id="r_soft_reason"></span></p>
    
    <hr style="border-top: 1px solid #333; margin: 15px 0;">

    <p><b>Plan Sugerido:</b> <span id="r_plan"></span></p>
    <p style="font-size: 0.9em; color: #aaa;">Motivo: <span id="r_plan_reason"></span></p>

    <a id="btn-crear" class="btn-create" href="#">
        Crear mi servidor con esta configuración →
    </a>
</div>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
<?php include_once "../../comun/chatbot.php"; ?>
</html>

<?php
session_start();
// Navbar incluido
include_once "../../comun/navbar.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Centro de Ayuda y Soporte - MineHostX</title>

<style>
/* --- Estilos Base --- */
body {
    background:#121212;
    color:#fff;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin:0;
    padding-top:60px; /* espacio para navbar fijo */
}

.container {
    max-width:1000px;
    margin:20px auto 60px;
    padding:30px;
    background:#1E1E1E;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
}

h1 {
    text-align:center;
    color:#FF9800;
    margin-bottom:10px;
    font-size:2.2em;
}
.tagline {
    text-align:center;
    color:#aaa;
    margin-bottom:30px;
}

/* --- Buscador --- */
.search-box {
    margin-bottom:30px;
}
.search-box input {
    width:100%;
    padding:14px;
    border-radius:10px;
    border:1px solid #333;
    background:#222;
    color:#fff;
    font-size:1em;
    box-sizing:border-box;
    transition:border-color 0.2s;
}
.search-box input:focus {
    border-color:#4FC3F7;
    background:#272727;
}

/* --- Categorías FAQ --- */
.category {
    margin-top:40px;
}

.category h2 {
    color:#4FC3F7;
    border-bottom:2px solid #333;
    padding-bottom:8px;
    margin-bottom:15px;
    font-size:1.6em;
}

.faq-item {
    background:#272727;
    margin:12px 0;
    padding:18px;
    border-radius:12px;
    cursor:pointer;
    transition:background 0.2s, box-shadow 0.2s;
}

.faq-item:hover {
    background:#303030;
    box-shadow:0 2px 8px rgba(0,0,0,0.4);
}

.question {
    font-weight:bold;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:1.1em;
    color:#fff;
}

.answer {
    display:none;
    padding-top:15px;
    color:#ccc;
    line-height:1.6em;
    border-top:1px dashed #333;
    margin-top:10px;
}
.answer b {
    color:#FF9800;
}

.icon {
    transition:transform 0.2s;
    font-size:1.2em;
    color:#4FC3F7;
}

.open .icon {
    transform:rotate(90deg);
    color:#FF9800;
}

/* --- Sección de Contacto Directo --- */
.contact-section {
    background:#222;
    padding:30px;
    margin-top:50px;
    border-radius:12px;
    text-align:center;
    border:1px solid #333;
}
.contact-section h3 {
    color:#FF9800;
    margin-top:0;
    font-size:1.5em;
}
.btn-contact {
    display:inline-block;
    background:#F44336;
    color:#fff;
    padding:12px 25px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    margin-top:15px;
    transition:background 0.2s;
}
.btn-contact:hover {
    background:#E57373;
}

/* --- Footer --- */
footer {
    background:#0d0d0d;
    color:#aaa;
    text-align:center;
    padding:20px;
    font-size:0.9em;
}
footer a {
    color:#4FC3F7;
    text-decoration:none;
}
</style>

<script>
// Expandir/Contraer FAQ
function toggleFAQ(el) {
    el.classList.toggle("open");
    let ans = el.querySelector(".answer");
    ans.style.display = ans.style.display === "block" ? "none" : "block";
}

// Buscar preguntas/respuestas
function searchFAQ() {
    let text = document.getElementById("search").value.toLowerCase();
    let items = document.querySelectorAll(".faq-item");
    let visibleCount = 0;

    items.forEach(item => {
        let q = item.querySelector(".question").innerText.toLowerCase();
        let a = item.querySelector(".answer").innerText.toLowerCase();
        if(q.includes(text) || a.includes(text)){
            item.style.display="block";
            visibleCount++;
        } else {
            item.style.display="none";
        }
    });

    let noResults = document.getElementById("no-results");
    if(visibleCount===0 && text.length>0){
        noResults.style.display='block';
    } else {
        noResults.style.display='none';
    }
}
</script>

</head>
<body>

<div class="container">
<h1>❓ Centro de Ayuda y Soporte</h1>
<p class="tagline">Encuentra respuestas rápidas a las dudas más comunes sobre hosting y Minecraft.</p>

<div class="search-box">
    <input type="text" id="search" placeholder="🔍 Escribe una palabra clave (ej: 'arrancar', 'mod', 'pago')..." onkeyup="searchFAQ()">
</div>

<div id="no-results" style="display:none; text-align:center; padding:20px; color:#F44336; border:1px dashed #F44336; border-radius:8px;">
    No encontramos resultados para tu búsqueda. Intenta usar palabras clave diferentes o contacta con soporte directo.
</div>

<!-- FAQ Categorías -->
<div class="category">
<h2>🖥️ Servidores</h2>
<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo enciendo o apago mi servidor? <span class="icon">▶</span></div>
    <div class="answer">
        Debes ir a la sección <b>Mis servidores</b>, seleccionar el servidor deseado y usar los botones <b>Iniciar</b> y <b>Detener</b>.
    </div>
</div>

<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Por qué mi servidor tarda en arrancar? <span class="icon">▶</span></div>
    <div class="answer">
        Es normal, especialmente con <b>Forge</b> o muchos mods/plugins. Puede tardar entre 30 y 90 segundos.
    </div>
</div>

<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo me conecto a mi servidor? <span class="icon">▶</span></div>
    <div class="answer">
        La IP y el puerto se muestran en <b>Detalles del Servidor</b>. Copia la dirección completa y pégala en Minecraft.
    </div>
</div>
</div>

<div class="category">
<h2>🧩 Mods y Plugins</h2>
<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo subo mods o plugins? <span class="icon">▶</span></div>
    <div class="answer">
        Ve a <b>Detalles del Servidor</b>. Según el software, sube tus <code>.jar</code> a <b>Archivos</b> o <b>Mods/Plugins</b>.
    </div>
</div>

<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Puedo usar mods y plugins a la vez? <span class="icon">▶</span></div>
    <div class="answer">
        ⚠️ No. Minecraft no soporta usar **Forge** y **Paper** simultáneamente. Elige un software base.
    </div>
</div>
</div>

<div class="category">
<h2>💾 Cuenta y Backups</h2>
<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo hago una copia de seguridad (backup)? <span class="icon">▶</span></div>
    <div class="answer">
        Ve a <b>Backups</b> en detalles del servidor y pulsa "Crear backup". Recomendado antes de instalar mods.
    </div>
</div>

<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo cambio mi contraseña? <span class="icon">▶</span></div>
    <div class="answer">
        Dirígete a <b>Perfil</b> o <b>Configuración de Cuenta</b> para actualizar tus datos.
    </div>
</div>
</div>

<div class="category">
<h2>💳 Pagos y Planes</h2>
<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">¿Cómo cambio o mejoro mi plan? <span class="icon">▶</span></div>
    <div class="answer">
        En <b>Mi Plan</b> selecciona el nuevo plan y completa el proceso de pago simulado.
    </div>
</div>

<div class="faq-item" onclick="toggleFAQ(this)">
    <div class="question">El proceso de pago me da error <span class="icon">▶</span></div>
    <div class="answer">
        Recuerda: MineHostX es simulado y no usamos dinero real. Verifica que los campos del formulario estén completos.
    </div>
</div>
</div>

<div class="contact-section">
<h3>¿No encuentras lo que buscas?</h3>
<p>Nuestro equipo de soporte está listo para ayudarte con problemas técnicos específicos.</p>
<a href="mailto:soporte@minehostx.local" class="btn-contact">📧 Contactar con Soporte Directo</a>
</div>

</div>

<footer>
 © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
 <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include_once "../../comun/chatbot.php"; ?>
</body>
</html>

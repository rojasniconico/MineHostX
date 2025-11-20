<?php include_once "../../comun/header.php"; ?>
<link rel="stylesheet" href="../../archivos/css/estilos.css">
<?php include_once "../../comun/navbar.php"; ?>


<div class="container" style="max-width: 800px; margin: 40px auto; padding: 30px; background: #1E1E1E; color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
    
    <h2 style="text-align:center; color:#FF9800; margin-bottom: 20px;">📩 Contáctanos</h2>

    <div class="card" style="background: #272727; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
        <p style="margin-bottom: 20px;">Si deseas más información o tienes alguna duda, completa el siguiente formulario:</p>

        <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="text" name="name" placeholder="Tu nombre" required style="padding: 12px; border-radius: 8px; border: 1px solid #333; background:#222; color:#fff;">
            <input type="email" name="email" placeholder="Tu correo electrónico" required style="padding: 12px; border-radius: 8px; border: 1px solid #333; background:#222; color:#fff;">
            <textarea name="message" placeholder="Escribe tu mensaje..." rows="5" required style="padding: 12px; border-radius: 8px; border: 1px solid #333; background:#222; color:#fff;"></textarea>
            <button type="submit" style="padding: 12px; border-radius: 8px; border: none; background: #4FC3F7; color: #121212; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                Enviar mensaje
            </button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            echo '<p style="margin-top:15px; color:#4CAF50; font-weight:bold;">✅ Gracias por contactar con nosotros. Te responderemos pronto.</p>';
        }
        ?>

        <h3 style="margin-top: 30px; color:#FF9800;">📍 Información de contacto</h3>
        <p>
            Email: <a href="mailto:soporte@minehostx.local" style="color:#4FC3F7;">soporte@minehostx.local</a><br>
            Teléfono: +34 600 123 456<br>
            Dirección: Calle del Servidor Nº5, Vigo, España
        </p>
    </div>
</div>

<?php include_once "../../comun/footer.php"; ?>

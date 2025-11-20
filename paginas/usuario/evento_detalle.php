<?php
// Incluye archivos base
session_start();
require_once "../../db.php";

// Requerimos una función de comprobación de permisos de admin si existe, si no, lo dejamos sin admin check.
// Asumiremos que el usuario ya está autenticado.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// 1. Validar y obtener el ID del evento de la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: eventos.php");
    exit();
}

$event_id = intval($_GET['id']);

// 2. Consultar la DB para obtener los detalles del evento (incluyendo start_at)
$sql_evento = "
    SELECT 
        id, name, description, reward, event_type, 
        created_at, ends_at, start_at /* <-- RECUERDA INCLUIR start_at */
    FROM events
    WHERE id = ? AND active = 1
";

$stmt = $conn->prepare($sql_evento);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();
$stmt->close();

if (!$evento) {
    header("Location: eventos.php");
    exit();
}

// -----------------------------------------------------------
// 3. Lógica de Tiempo y Bloqueo
// -----------------------------------------------------------

// Usaremos las fechas en formato UNIX timestamp para pasarlas a JavaScript
$fecha_inicio = new DateTime($evento['start_at']);
$fecha_fin = new DateTime($evento['ends_at']);

$ahora = new DateTime();
$terminado = $fecha_fin < $ahora;
$proximo = $fecha_inicio > $ahora; // NUEVO: Bloqueo por evento futuro

// B) Estado de participación del usuario (Lógica que deseas mantener)
$sql_participacion = "
    SELECT id
    FROM event_participants
    WHERE event_id = ? AND user_id = ?
";
$stmt_part = $conn->prepare($sql_participacion);
$stmt_part->bind_param("ii", $event_id, $user_id);
$stmt_part->execute();
$result_part = $stmt_part->get_result();
$ya_inscrito = $result_part->num_rows > 0;
$stmt_part->close();

/**
 * Función auxiliar para formatear el tiempo restante.
 */
function formatTiempoRestante($diff) {
    if ($diff->y > 0) return $diff->y . ' años';
    if ($diff->m > 0) return $diff->m . ' meses';
    if ($diff->d > 0) return $diff->d . ' días';
    if ($diff->h > 0) return $diff->h . ' horas';
    if ($diff->i > 0) return $diff->i . ' minutos';
    return 'Menos de 1 minuto';
}

// -----------------------------------------------------------
// 4. Procesar participación (Se mantiene la lógica original)
// -----------------------------------------------------------
$mensaje_participacion = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participar'])) {
    
    if ($proximo) { // NUEVO: Bloqueo si el evento es próximo
        $mensaje_participacion = "<p class='mensaje-error'>❌ El evento comienza el **{$fecha_inicio->format('d/m/Y H:i')}**. ¡Aún no puedes unirte!</p>";
    } elseif ($terminado) {
        $mensaje_participacion = "<p class='mensaje-error'>❌ El evento ha finalizado y ya no puedes unirte.</p>";
    } elseif ($ya_inscrito) {
        $mensaje_participacion = "<p class='mensaje-error'>❗ Ya estás inscrito en este evento.</p>";
    } else {
        // Lógica de inscripción
        $sql_insert = "
            INSERT INTO event_participants (event_id, user_id) 
            VALUES (?, ?)
        ";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $event_id, $user_id);
        
        if ($stmt_insert->execute()) {
            $ya_inscrito = true; 
            $mensaje_participacion = "<p class='mensaje-exito'>✅ ¡Te has inscrito exitosamente al evento!</p>";
        } else {
            $mensaje_participacion = "<p class='mensaje-error'>❌ Error al intentar inscribirte. Inténtalo de nuevo.</p>";
        }
        $stmt_insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['name']); ?> | MineHostX</title>

    <style>
        /* CSS Base (manteniendo la estructura) */
        body {
            background:#121212;
            color:#fff;
            font-family:"Segoe UI";
            margin:0;
            padding:0;
        }

        .main-container {
            max-width:800px;
            margin:40px auto;
            background:#1E1E1E; 
            padding:30px;
            border-radius:16px;
            box-shadow:0 6px 18px rgba(0,0,0,0.5);
        }

        h1 {
            color:#4FC3F7; 
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .evento-detalle-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .detalle-box {
            background: #191919; 
            padding: 20px;
            border-radius: 10px;
        }
        
        .label { 
            color:#4FC3F7; 
            font-weight:bold; 
            display: block;
            margin-bottom: 5px;
        }
        
        .valor {
            color: #fff;
            font-size: 1.1em;
            margin-bottom: 15px;
        }

        /* Estilo para el botón de participación */
        .btn-participar {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.2s;
            text-decoration: none;
            text-align: center;
            display: block;
        }

        .btn-participar.activo {
            background: #4FC3F7; 
            color: #000;
        }

        .btn-participar.activo:hover {
            background-color: #79D1FF;
        }

        .btn-participar.inactivo, .btn-participar.terminado, .btn-participar.proximo {
            background: #333;
            color: #aaa;
            cursor: not-allowed;
        }
        
        .btn-participar.proximo { /* Estilo específico para evento futuro bloqueado */
            border: 1px solid #E57373;
            color: #E57373;
        }


        /* Mensajes de feedback (Mantener) */
        .mensaje-exito, .mensaje-error {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
        .mensaje-exito {
            background-color: #008800;
        }
        .mensaje-error {
            background-color: #AA0000;
        }
        
        .reward-tag {
            display: inline-block;
            background: #333;
            color: #4FC3F7;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Diseño para dispositivos pequeños */
        @media (min-width: 600px) {
            .evento-detalle-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

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

    </style>
</head>
<body>

<?php include_once "../../comun/navbar.php"; ?>

<div class="main-container">

    <h1><?php echo htmlspecialchars($evento['name']); ?></h1>

    <?php echo $mensaje_participacion; ?>

    <div class="evento-detalle-grid">
        
        <div class="detalle-box">
            <span class="label">📜 Descripción del Evento</span>
            <div class="valor" style="white-space: pre-wrap;"><?php echo htmlspecialchars($evento['description']); ?></div>

            <span class="label">🎁 Recompensa</span>
            <span class="reward-tag"><?php echo htmlspecialchars($evento['reward'] ?: 'No especificada'); ?></span>
        </div>

        <div class="detalle-box">
            
            <span class="label">⏰ Inicia</span>
            <p class="valor"><?php echo $fecha_inicio->format('d/m/Y H:i'); ?></p>
            
            <span class="label">🏁 Finaliza</span>
            <p class="valor"><?php echo $fecha_fin->format('d/m/Y H:i'); ?></p>
            
            <span class="label">⏳ Tiempo Restante</span>
            <p class="valor" id="countdown-timer" style="color:#4FC3F7; font-size:1.2em; font-weight:bold;">
                <?php echo $terminado ? '¡FINALIZADO!' : 'Calculando...'; ?>
            </p>
        </div>
    </div>
    
    <form method="POST">
        <?php if ($terminado): ?>
            <button class="btn-participar terminado" disabled>Evento Finalizado</button>
        <?php elseif ($proximo): ?>
            <button class="btn-participar proximo" disabled>🔒 Comienza el <?php echo $fecha_inicio->format('d/m/Y H:i'); ?></button>
        <?php elseif ($ya_inscrito): ?>
            <button class="btn-participar inactivo" disabled>✔ Ya Estás Inscrito</button>
        <?php else: ?>
            <input type="hidden" name="participar" value="1">
            <button type="submit" class="btn-participar activo">Participar en el Evento</button>
        <?php endif; ?>
    </form>

    <div style="text-align: center; margin-top: 25px;">
        <a href="eventos.php" style="color:#aaa; text-decoration:none;">← Volver a la lista de Eventos</a>
    </div>

</div>

<script>
    // Obtenemos la fecha de finalización del evento del PHP
    const targetDate = "<?php echo $evento['ends_at']; ?>";
    const countDownDate = new Date(targetDate).getTime();
    const timerElement = document.getElementById("countdown-timer");

    if (timerElement) {
        // Actualizar el contador cada 1 segundo
        const x = setInterval(function() {

            // Obtener la fecha y hora de hoy
            const now = new Date().getTime();
                
            // Encontrar la distancia entre ahora y la fecha de la cuenta regresiva
            const distance = countDownDate - now;
                
            // Cálculo del tiempo para días, horas, minutos y segundos
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
            // Muestra el resultado
            if (distance > 0) {
                timerElement.innerHTML = 
                    (days > 0 ? days + "d " : "") + 
                    hours + "h " + 
                    minutes + "m " + 
                    seconds + "s";
            }
                
            // Si la cuenta regresiva ha terminado, detiene el contador
            if (distance < 0) {
                clearInterval(x);
                timerElement.innerHTML = "¡FINALIZADO!";
                // Opcionalmente, recargar la página para actualizar el botón
                window.location.reload(); 
            }
        }, 1000);
    }
</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>
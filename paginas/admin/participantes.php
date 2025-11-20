<?php
session_start();
// La ruta es la misma que usaste en gestion.php
require_once "../../db.php"; 

// --- VERIFICACIÓN DE PERMISOS DE ADMINISTRACIÓN ---
$staff_ids = [1, 5, 10];
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["user_id"], $staff_ids)) {
    die("<div style='background: #121212; color: #E57373; padding: 20px; font-family: \"Segoe UI\";'>Acceso denegado. Se requieren permisos de Staff.</div>");
}
// --- FIN VERIFICACIÓN ---

// 1. Obtener y validar el ID del evento
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("<div style='background: #121212; color: #E57373; padding: 20px; font-family: \"Segoe UI\";'>Error: ID de evento no proporcionado o no válido.</div>");
}

$event_id = intval($_GET['event_id']);
$event_name = "Evento Desconocido"; // Valor por defecto
$error_message = '';

// 2. Obtener detalles del evento
$sql_event = "SELECT name, description, start_at FROM events WHERE id = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$result_event = $stmt_event->get_result();

if ($row_event = $result_event->fetch_assoc()) {
    $event_name = htmlspecialchars($row_event['name']);
    $event_description = htmlspecialchars($row_event['description']);
    $event_start = (new DateTime($row_event['start_at']))->format('d/m/Y H:i');
} else {
    $error_message = "<div class='alerta error'>No se encontró el evento con ID: {$event_id}.</div>";
}
$stmt_event->close();

// 3. Obtener la lista de participantes (JOIN con la tabla de usuarios)
// 🚀 CORRECCIÓN: Usamos 'ep.created_at' en lugar de 'ep.joined_at'
$sql_participants = "
    SELECT u.id AS user_id, u.username, u.email, ep.created_at
    FROM event_participants ep
    JOIN users u ON ep.user_id = u.id
    WHERE ep.event_id = ?
    ORDER BY ep.created_at ASC";

$stmt_participants = $conn->prepare($sql_participants);
$stmt_participants->bind_param("i", $event_id);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();
$total_participants = $result_participants->num_rows;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Participantes de Evento | Admin</title>
    <style>
        /* Reutilizamos los estilos base de tu gestion.php */
        body { font-family: "Segoe UI", Arial, sans-serif; background: #121212; color: #fff; margin: 0; }
        .admin-container { max-width: 1000px; margin: 50px auto; background: #1E1E1E; padding: 30px; border-radius: 16px; box-shadow: 0 6px 18px rgba(0,0,0,0.5); }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; color: #4FC3F7; }
        .btn-panel { display: inline-block; background: #4FC3F7; color: #000; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-bottom: 20px; transition: background-color 0.2s; }
        .btn-panel:hover { background: #79D1FF; }
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; }
        .error { background: #4b1a1c; color: #F8BBD0; border: 1px solid #c62828; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #191919; border-radius: 8px; overflow: hidden; }
        table th, table td { border: 1px solid #333; padding: 12px; text-align: left; color: #fff; }
        table th { background-color: #2D2D2D; color: #4FC3F7; }
        
        .event-info {
            background: #2D2D2D;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 5px solid #4FC3F7;
        }
        .event-info p { margin: 5px 0; }
        .event-info strong { color: #A5D6A7; }
        
    </style>
</head>
<body>

<div class="admin-container">
    
    <a href="gestion_eventos.php" class="btn-panel">⬅️ Volver a Gestión de Eventos</a>
    
    <h2>👥 Participantes del Evento: <?php echo $event_name; ?></h2>

    <?php echo $error_message; ?>

    <?php if (!$error_message): ?>
        <div class="event-info">
            <p><strong>Descripción:</strong> <?php echo $event_description; ?></p>
            <p><strong>Inicio:</strong> <?php echo $event_start; ?></p>
            <p><strong>Total de Inscritos:</strong> <strong style="color: #FFD54F;"><?php echo $total_participants; ?></strong> usuarios</p>
        </div>

        <?php if ($total_participants > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID de Usuario</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Fecha de Inscripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($participante = $result_participants->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $participante['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($participante['username']); ?></td>
                        <td><?php echo htmlspecialchars($participante['email']); ?></td>
                        <td><?php echo (new DateTime($participante['created_at']))->format('d/m/Y H:i:s'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class='alerta' style="background: #333; color: #fff; border: 1px solid #666;">
                Aún no hay participantes registrados para este evento.
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
<?php
$stmt_participants->close();
// mysqli_close($conn); // Cierra la conexión si no se usa más en la página
?>
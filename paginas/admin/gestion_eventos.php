<?php
session_start();
// Asegúrate de que esta ruta sea correcta para tu archivo db.php
require_once "../../db.php"; 

// --- VERIFICACIÓN DE PERMISOS DE ADMINISTRACIÓN ---
// IMPORTANTE: Modifica este array con los IDs de usuario que tienen permiso de staff.
$staff_ids = [1, 5, 10]; 
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["user_id"], $staff_ids)) {
    die("<div style='background: #121212; color: #E57373; padding: 20px; font-family: \"Segoe UI\";'>Acceso denegado. Se requieren permisos de Staff.</div>");
}
// --- FIN VERIFICACIÓN ---

// Inicializar variables de formulario
$id = $name = $description = $reward = $event_type = $start_at = $ends_at = '';
$active = 1;
$mensaje = '';

// --------------------------------------------------------------------------
// LÓGICA DE PROCESAMIENTO (CREATE / UPDATE / DELETE)
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Función de limpieza básica (usamos prepared statements después)
    $clean_post = function($key) use ($conn) {
        return isset($_POST[$key]) ? mysqli_real_escape_string($conn, $_POST[$key]) : '';
    };

    $action = $clean_post('action');
    $id = intval($clean_post('id'));
    
    if ($action === 'create' || $action === 'update') {
        // Recoger datos del formulario
        $name = $clean_post('name');
        $description = $clean_post('description');
        $reward = $clean_post('reward');
        $event_type = $clean_post('event_type');
        $start_at = $clean_post('start_at');
        $ends_at = $clean_post('ends_at');
        $active = isset($_POST['active']) ? 1 : 0;
        
        if (empty($name) || empty($start_at) || empty($ends_at)) {
            $mensaje = "<div class='alerta error'>El nombre, inicio y fin son obligatorios.</div>";
        } else {
            if ($action === 'create') {
                $sql = "INSERT INTO events (name, description, reward, event_type, start_at, ends_at, active) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $description, $reward, $event_type, $start_at, $ends_at, $active);
                $exito = $stmt->execute();
                $mensaje = $exito ? "<div class='alerta exito'>Evento creado con éxito.</div>" : "<div class='alerta error'>Error al crear: " . $stmt->error . "</div>";
                $stmt->close();
            } elseif ($action === 'update' && $id > 0) {
                $sql = "UPDATE events SET name=?, description=?, reward=?, event_type=?, start_at=?, ends_at=?, active=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssii", $name, $description, $reward, $event_type, $start_at, $ends_at, $active, $id);
                $exito = $stmt->execute();
                $mensaje = $exito ? "<div class='alerta exito'>Evento actualizado con éxito.</div>" : "<div class='alerta error'>Error al actualizar: " . $stmt->error . "</div>";
                $stmt->close();
            }
        }
    } elseif ($action === 'delete' && $id > 0) {
        // Desactivar evento (Más seguro que borrar permanentemente)
        $sql = "UPDATE events SET active = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $exito = $stmt->execute();
        $mensaje = $exito ? "<div class='alerta exito'>Evento desactivado con éxito.</div>" : "<div class='alerta error'>Error al desactivar: " . $stmt->error . "</div>";
        $stmt->close();
    }
    
    // Limpiar variables del formulario después de una acción exitosa
    if (strpos($mensaje, 'éxito') !== false) {
        $id = $name = $description = $reward = $event_type = $start_at = $ends_at = '';
        $active = 1;
    }
}

// LÓGICA DE EDICIÓN: Si se solicita editar un evento
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $sql_edit = "SELECT * FROM events WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($row_edit = $result_edit->fetch_assoc()) {
        $id = $row_edit['id'];
        $name = $row_edit['name'];
        $description = $row_edit['description'];
        $reward = $row_edit['reward'];
        $event_type = $row_edit['event_type'];
        // Formato para input datetime-local
        $start_at = (new DateTime($row_edit['start_at']))->format('Y-m-d\TH:i');
        $ends_at = (new DateTime($row_edit['ends_at']))->format('Y-m-d\TH:i');
        $active = $row_edit['active'];
    }
    $stmt_edit->close();
}


// --------------------------------------------------------------------------
// CONSULTAR EVENTOS PARA LA LISTA
// --------------------------------------------------------------------------
$sql_eventos_lista = "SELECT id, name, event_type, start_at, ends_at, active FROM events ORDER BY start_at DESC";
$result_eventos_lista = mysqli_query($conn, $sql_eventos_lista);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Eventos | Admin</title>
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

        /* Adaptación al tema oscuro */
        body { 
            font-family: "Segoe UI", Arial, sans-serif; 
            background: #121212; /* Fondo principal oscuro */
            color: #fff; /* Texto blanco */
            margin: 0;
        }
        .admin-container { 
            max-width: 1000px; 
            margin: 50px auto; /* Volvemos a usar el margen superior para centrar */
            background: #1E1E1E; /* Contenedor oscuro principal */
            padding: 30px; 
            border-radius: 16px; 
            box-shadow: 0 6px 18px rgba(0,0,0,0.5); /* Sombra intensa */
        }
        h2 { 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
            color: #4FC3F7; /* Color de acento */
        }
        /* Estilo para el botón de navegación a panel */
        .btn-panel {
            display: inline-block;
            background: #4FC3F7; 
            color: #000;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            transition: background-color 0.2s;
        }
        .btn-panel:hover {
            background: #79D1FF;
        }
        
        /* Resto de estilos */
        .formulario-gestion label { display: block; margin-top: 15px; font-weight: bold; color: #4FC3F7; }
        .formulario-gestion input[type="text"], .formulario-gestion textarea, .formulario-gestion input[type="datetime-local"], .formulario-gestion select { width: 100%; padding: 10px; margin-top: 5px; background: #2D2D2D; color: #fff; border: 1px solid #444; border-radius: 4px; box-sizing: border-box; }
        .formulario-gestion button[type="submit"] { background: #4FC3F7; color: #000; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px; font-weight: bold; transition: background-color 0.2s; }
        .formulario-gestion button[type="submit"]:hover { background: #79D1FF; }
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; }
        .exito { background: #1f3f21; color: #A5D6A7; border: 1px solid #2e7d32; }
        .error { background: #4b1a1c; color: #F8BBD0; border: 1px solid #c62828; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #191919; border-radius: 8px; overflow: hidden; }
        table th, table td { border: 1px solid #333; padding: 12px; text-align: left; }
        table th { background-color: #2D2D2D; color: #4FC3F7; }
        .btn-accion { padding: 8px 12px; margin-right: 5px; text-decoration: none; border-radius: 4px; font-size: 0.9em; display: inline-block; transition: background-color 0.2s; font-weight: bold; }
        .btn-editar { background: #ffc107; color: #333; }
        .btn-desactivar { background: #E57373; color: #333; }
        .estado-activo { color: #A5D6A7; font-weight: bold; }
        .estado-inactivo { color: #E57373; }
        
        /* 🚀 NUEVO ESTILO PARA PARTICIPANTES */
        .btn-participantes {
            background: #00BCD4; /* Color Cían para distinguir */
            color: #333;
        }
        .btn-participantes:hover {
            background: #4DD0E1;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <a href="../usuario/panel.php" class="btn-panel">🏠 Volver al Panel Admin</a>
    
    <h2><?php echo $id ? 'Editar Evento: ' . htmlspecialchars($name) : 'Crear Nuevo Evento'; ?></h2>

    <?php echo $mensaje; ?>

    <form method="POST" class="formulario-gestion">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="action" value="<?php echo $id ? 'update' : 'create'; ?>">

        <label for="name">Nombre:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="description">Descripción:</label>
        <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>

        <label for="reward">Recompensa (Opcional):</label>
        <input type="text" name="reward" id="reward" value="<?php echo htmlspecialchars($reward); ?>">
        
        <label for="event_type">Tipo de Evento:</label>
        <select name="event_type" id="event_type">
            <option value="Comunidad" <?php echo $event_type == 'Comunidad' ? 'selected' : ''; ?>>Comunidad</option>
            <option value="Empresa" <?php echo $event_type == 'Empresa' ? 'selected' : ''; ?>>Empresa</option>
        </select>
        
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <label for="start_at">Fecha y Hora de Inicio:</label>
                <input type="datetime-local" name="start_at" id="start_at" value="<?php echo $start_at; ?>" required>
            </div>
            <div style="flex: 1;">
                <label for="ends_at">Fecha y Hora de Fin:</label>
                <input type="datetime-local" name="ends_at" id="ends_at" value="<?php echo $ends_at; ?>" required>
            </div>
        </div>
        
        <label style="margin-top: 20px; color: #fff;">
            <input type="checkbox" name="active" <?php echo $active ? 'checked' : ''; ?>> **Activo** (Se muestra en la web principal)
        </label>

        <button type="submit"><?php echo $id ? 'Actualizar Evento' : 'Crear Evento'; ?></button>
        <?php if ($id): ?>
            <a href="gestion.php" style="margin-left: 10px; color: #79D1FF; text-decoration: none;">Cancelar Edición</a>
        <?php endif; ?>
    </form>
    
    <hr style="margin: 30px 0; border-color: #333;">

    <h2>Lista de Eventos Existentes</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Inicia</th>
                <th>Finaliza</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($evento_lista = mysqli_fetch_assoc($result_eventos_lista)): ?>
            <tr>
                <td><?php echo $evento_lista['id']; ?></td>
                <td><?php echo htmlspecialchars($evento_lista['name']); ?></td>
                <td><?php echo htmlspecialchars($evento_lista['event_type']); ?></td>
                <td><?php echo (new DateTime($evento_lista['start_at']))->format('d/m H:i'); ?></td>
                <td><?php echo (new DateTime($evento_lista['ends_at']))->format('d/m H:i'); ?></td>
                <td><span class="<?php echo $evento_lista['active'] ? 'estado-activo' : 'estado-inactivo'; ?>">
                    <?php echo $evento_lista['active'] ? 'Activo' : 'Inactivo'; ?>
                </span></td>
                <td>
                    <a href="participantes.php?event_id=<?php echo $evento_lista['id']; ?>" class="btn-accion btn-participantes">👥 Participantes</a>
                    
                    <a href="?edit=<?php echo $evento_lista['id']; ?>" class="btn-accion btn-editar">Editar</a>
                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('¿Seguro que deseas desactivar este evento?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $evento_lista['id']; ?>">
                        <button type="submit" class="btn-accion btn-desactivar">Desactivar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>
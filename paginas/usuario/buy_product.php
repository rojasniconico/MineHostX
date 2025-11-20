<?php
session_start();
require_once "../../db.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$product_id = intval($_GET['id'] ?? 0);
$method = $_GET['method'] ?? '';

if ($product_id <= 0 || $method !== 'points') {
    header("Location: tienda.php?msg=error_param");
    exit();
}

// 1. Iniciar transacción para asegurar la integridad de los datos
$conn->begin_transaction();

try {
    // 2. Obtener producto y puntos del usuario en una sola consulta
    $sql = "SELECT p.name, p.cost_points, u.points 
            FROM store_products p, users u 
            WHERE p.id = ? AND u.id = ? AND p.active = TRUE";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        throw new Exception("Producto no encontrado o inactivo.");
    }

    $product_name = $data['name'];
    $cost = $data['cost_points'];
    $current_points = $data['points'];

    if (is_null($cost) || $cost <= 0) {
        throw new Exception("Este producto no se puede comprar con puntos.");
    }

    // 3. Verificar saldo suficiente
    if ($current_points < $cost) {
        throw new Exception("Puntos insuficientes. Necesitas " . number_format($cost) . " pts.");
    }

    // 4. Actualizar puntos del usuario (restar costo)
    $sql_update = "UPDATE users SET points = points - ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $cost, $user_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Error al actualizar los puntos.");
    }
    $stmt_update->close();

    // 5. REGISTRAR LA COMPRA (Debes tener una tabla para esto, ej: user_purchases)
    // Recomendación: Añadir lógica para aplicar el beneficio del producto aquí.
    // Ej: aplicar el boost de RAM.

    // 6. Confirmar transacción
    $conn->commit();
    header("Location: tienda.php?msg=success&p=" . urlencode($product_name));

} catch (Exception $e) {
    // Revertir cualquier cambio en caso de error
    $conn->rollback();
    header("Location: tienda.php?msg=error&detail=" . urlencode($e->getMessage()));
}

// Cierre de la conexión
mysqli_close($conn);
?>
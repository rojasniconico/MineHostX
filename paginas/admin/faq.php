<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../usuario/panel.php");
    exit();
}

$faqs = mysqli_query($conn, "SELECT * FROM faq ORDER BY category ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar FAQ - MineHostX</title>

<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; }
table { width:90%; margin:20px auto; border-collapse:collapse; }
th, td { padding:10px; border-bottom:1px solid #333; text-align:left; }
a { color:#4FC3F7; text-decoration:none; }
.btn { background:#4FC3F7; padding:6px 12px; border-radius:6px; color:#000; font-weight:bold; }
.btn-red { color:#ff4d4d; }
.center { text-align:center; }
</style>
</head>
<body>
<br><br>
<h2 class="center" style="color:#4FC3F7;">📘 Administrar Preguntas Frecuentes</h2>

<p class="center">
    <a class="btn" href="faq_nueva.php">➕ Crear nueva pregunta</a>
</p>

<table>
<tr>
    <th>ID</th>
    <th>Categoría</th>
    <th>Pregunta</th>
    <th>Acciones</th>
</tr>

<?php while ($f = mysqli_fetch_assoc($faqs)): ?>
<tr>
    <td><?php echo $f["id"]; ?></td>
    <td><?php echo htmlspecialchars($f["category"]); ?></td>
    <td><?php echo htmlspecialchars($f["question"]); ?></td>
    <td>
        <a href="faq_editar.php?id=<?php echo $f['id']; ?>">✏ Editar</a> |
        <a href="faq_borrar.php?id=<?php echo $f['id']; ?>" class="btn-red">🗑 Borrar</a>
    </td>
</tr>
<?php endwhile; ?>

</table>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>
</body>
</html>


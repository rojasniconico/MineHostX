<?php
session_start();
require_once "../../db.php";
include_once "../../comun/navbar.php";

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../autenticacion/login.php");
    exit();
}

// Añadir nuevo conocimiento
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["new_question"])) {
    $q = trim($_POST["new_question"]);
    $a = trim($_POST["new_answer"]);
    $c = trim($_POST["category"]);

    if ($q !== "" && $a !== "") {
        $sql = "INSERT INTO chatbot_knowledge (question, answer, category)
                VALUES ('$q', '$a', '$c')";
        mysqli_query($conn, $sql);
        $msg = "✅ Entrada añadida correctamente.";
    } else {
        $msg = "❌ Debes rellenar todos los campos.";
    }
}

// Borrar entrada
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    mysqli_query($conn, "DELETE FROM chatbot_knowledge WHERE id=$id");
    header("Location: chatbot_admin.php");
    exit();
}

// Obtener base de conocimiento
$data = mysqli_query($conn, "SELECT * FROM chatbot_knowledge ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Chatbot Admin - MineHostX</title>
<link rel="stylesheet" href="../../archivos/css/panel.css">
<style>
table {
  width: 100%;
  color: #fff;
}
td, th {
  padding: 10px;
  border-bottom: 1px solid #333;
}
input, textarea, select {
  width: 95%;
  padding: 10px;
  margin: 5px 0;
  border-radius: 6px;
  border: none;
}
button {
  padding: 10px 15px;
  border: none;
  background: #4FC3F7;
  color: #000;
  border-radius: 6px;
  cursor: pointer;
}
.btn-del {
  background: #E53935;
  color: white;
}
</style>
</head>
<body>
<div class="container">
  <h2>🤖 Administrar Chatbot Inteligente</h2>

  <?php if ($msg) echo "<p style='color:#4FC3F7;'>$msg</p>"; ?>

  <h3>➕ Añadir nuevo conocimiento</h3>

  <form method="POST">
    <input type="text" name="new_question" placeholder="Pregunta que el usuario hará" required>
    <textarea name="new_answer" placeholder="Respuesta del chatbot" required></textarea>

    <select name="category">
      <option value="general">General</option>
      <option value="planes">Planes</option>
      <option value="servidores">Servidores</option>
      <option value="tecnico">Técnico</option>
      <option value="soporte">Soporte</option>
      <option value="saludo">Saludos</option>
    </select>

    <button type="submit">Añadir</button>
  </form>

  <h3 style="margin-top:40px;">📚 Base de conocimiento</h3>

  <table>
    <tr>
      <th>ID</th>
      <th>Pregunta</th>
      <th>Respuesta</th>
      <th>Categoría</th>
      <th>Acciones</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($data)): ?>
    <tr>
      <td><?= $row["id"] ?></td>
      <td><?= $row["question"] ?></td>
      <td><?= $row["answer"] ?></td>
      <td><?= $row["category"] ?></td>
      <td>
        <a href="?delete=<?= $row['id'] ?>">
          <button class="btn-del">Eliminar</button>
        </a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

</div>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

</body>
</html>

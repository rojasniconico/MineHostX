<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../usuario/panel.php");
    exit();
}

$id = intval($_GET["id"]);
$faq = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM faq WHERE id=$id"));

if (!$faq) {
    die("❌ No existe la pregunta.");
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = mysqli_real_escape_string($conn, $_POST["category"]);
    $question = mysqli_real_escape_string($conn, $_POST["question"]);
    $answer = mysqli_real_escape_string($conn, $_POST["answer"]);

    mysqli_query($conn, "
        UPDATE faq
        SET category='$category', question='$question', answer='$answer'
        WHERE id=$id
    ");

    $msg = "✅ Pregunta actualizada.";
    $faq = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM faq WHERE id=$id"));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Pregunta</title>

<style>
body { background:#121212; color:#fff; font-family:'Segoe UI'; text-align:center; }
form {
    background:#1E1E1E; padding:20px; margin:30px auto;
    width:400px; border-radius:10px;
}
input, textarea {
    width:100%; padding:10px; margin:10px 0;
    border:none; border-radius:6px; background:#222; color:#fff;
}
button {
    background:#4FC3F7; padding:10px 20px; border:none;
    border-radius:8px; cursor:pointer; color:#000; font-weight:bold;
}
a { color:#4FC3F7; }
</style>

</head>
<body>

<h2>Editar Pregunta FAQ</h2>

<form method="POST">
    <input type="text" name="category" value="<?php echo htmlspecialchars($faq['category']); ?>" required>
    <input type="text" name="question" value="<?php echo htmlspecialchars($faq['question']); ?>" required>
    <textarea name="answer" rows="5" required><?php echo htmlspecialchars($faq['answer']); ?></textarea>

    <button type="submit">Guardar cambios</button>
    <p><?php echo $msg; ?></p>
</form>

<p><a href="faq.php">⬅ Volver</a></p>

</body>
</html>

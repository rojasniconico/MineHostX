<?php
session_start();
require_once "../../db.php";
if ($_SESSION['role']!=='admin') header("Location: ../../autenticacion/login.php");

$res = mysqli_query($conn, "SELECT p.*, u.username, pl.name AS plan_name FROM payments p
  LEFT JOIN users u ON p.user_id = u.id
  LEFT JOIN plans pl ON p.plan_id = pl.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Pagos</title></head><body>
<h2>Pagos (simulados)</h2>
<table border=1><tr><th>ID</th><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Date</th></tr>
<?php while($r=mysqli_fetch_assoc($res)): ?>
<tr><td><?=$r['id']?></td><td><?=htmlspecialchars($r['username'])?></td><td><?=htmlspecialchars($r['plan_name'])?></td><td><?=$r['amount']?></td><td><?=$r['status']?></td><td><?=$r['created_at']?></td></tr>
<?php endwhile; ?>
</table>
</body></html>

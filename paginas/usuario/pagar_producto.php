<?php
session_start();
// Asegúrate de que esta ruta sea correcta para tu archivo db.php
require_once "../../db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$product_id = intval($_GET['product_id'] ?? 0);

// --- OBTENER PRODUCTO ---
$product = null;
if ($product_id) {
    // 1. Consulta la tabla store_products, no plans
    $res = mysqli_query($conn, "SELECT * FROM store_products WHERE id=$product_id LIMIT 1");
    $product = mysqli_fetch_assoc($res);
}
// 2. Verificar que el producto exista y tenga un precio en dinero
if (!$product || is_null($product['cost_money']) || $product['cost_money'] <= 0) {
    die("Producto no válido o solo canjeable con puntos.");
}

// 3. Opcional: Si el producto es de tipo "único" (ej. IP dedicada)
// puedes añadir una verificación aquí para ver si el usuario ya lo posee
// (Necesitarías consultar tu tabla user_purchases o similar)

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pagar Producto — MineHostX Tienda</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
/* --- ESTILOS (Mantenemos los mismos estilos de pago de plan) --- */
.page {
  min-height: 100vh;
  background: linear-gradient(180deg,#0f0f10,#111217);
  color: #fff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  padding: 28px 16px;
}
.container {
  max-width: 980px;
  margin: 24px auto;
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 20px;
  align-items: start;
}

/* --- Plan/Product card --- */
.product-card {
  background: linear-gradient(180deg,#151515,#1f1f1f);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.6);
}
.product-title { color:#00BCD4; /* Color de acento para producto */ font-size:1.4rem; margin-bottom:6px; }
.product-price { font-size:1.1rem; font-weight:700; margin:8px 0; color:#fff; }
.product-desc { color:#bdbdbd; margin-bottom:14px; line-height:1.4; }

/* features (adaptado a producto) */
.features { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
.feature { background:#0f0f10; padding:8px 10px; border-radius:8px; color:#ddd; font-size:0.95rem; }

/* --- Payment form (right) --- */
.pay-card {
  background: linear-gradient(180deg,#111,#171717);
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.6);
}

/* card visual */
.card-visual {
  background: linear-gradient(135deg,#2d2f4a,#25304a);
  border-radius:10px;
  color:#fff;
  padding:14px;
  margin-bottom:12px;
  position: relative;
  overflow: hidden;
}
.card-visual .chip { width:42px;height:30px;border-radius:6px;background:linear-gradient(180deg,#ffd27a,#ffb74d); display:inline-block; margin-bottom:8px }
.card-number-visual { font-family: monospace; letter-spacing:2px; font-size:1.05rem; margin-top:6px; }
.card-meta { display:flex; justify-content:space-between; margin-top:10px; color:#cfcfcf; font-size:0.9rem; }

/* form inputs */
.form-row { margin-bottom:12px; }
.input {
  width:100%;
  padding:12px 12px;
  border-radius:8px;
  border:1px solid rgba(255,255,255,0.06);
  background:#0e0e0e;
  color:#fff;
  font-size:0.95rem;
  box-sizing:border-box;
}
.row-inline { display:flex; gap:10px; }
.col-2 { flex:1; }

/* submit */
.btn {
  display:inline-block;
  background: #4FC3F7;
  color: #000;
  text-decoration:none;
  font-weight:800;
  padding:10px 16px;
  border-radius:10px;
  border:none;
  cursor:pointer;
  margin-top:6px;
}

/* small */
.hint { color:#a8a8a8; font-size:0.9rem; margin-top:8px; }

/* responsive */
@media (max-width:900px) {
  .container { grid-template-columns: 1fr; padding: 0 10px; }
  .pay-card { order: 2; }
  .product-card { order: 1; }
}
</style>
</head>
<body>
<?php include_once "../../comun/navbar.php"; // Asumo que tienes un navbar común ?>

<div class="page">
  <div class="container">

        <div class="product-card">
      <div class="product-title">Pagar Producto: <?php echo htmlspecialchars($product['name']); ?></div>
      <div class="product-price">€ <?php echo number_format($product['cost_money'], 2); ?></div>
      <div class="product-desc"><?php echo htmlspecialchars($product['description'] ?? ''); ?></div>

      <div class="features">
        <div class="feature">Tipo: <?php echo htmlspecialchars($product['type']); ?></div>
        <?php if (!empty($product['duration_days'])): ?>
          <div class="feature">Duración: <?php echo intval($product['duration_days']); ?> días</div>
        <?php else: ?>
          <div class="feature">Permanente</div>
        <?php endif; ?>
        <div class="feature">Costo en Puntos: <?php echo intval($product['cost_points'] ?? 0); ?> pts</div>
      </div>

      <div class="hint">* Esto es una simulación de pago para la demo. No se procesará dinero real.</div>
    </div>

        <div class="pay-card">
      <div class="card-visual" id="cardVisual">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="chip" aria-hidden="true"></div>
          <div style="font-size:0.85rem; opacity:0.9;">MineHostX</div>
        </div>
        <div class="card-number-visual" id="cardNumberVisual">•••• •••• •••• ••••</div>
        <div class="card-meta">
          <div id="cardNameVisual">NOMBRE APELLIDOS</div>
          <div id="cardExpVisual">MM/AA</div>
        </div>
      </div>

            <form id="paymentForm" method="POST" action="procesar_pago_producto.php" novalidate>
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="amount" value="<?php echo $product['cost_money']; ?>">

        <div class="form-row">
          <label class="small">Nombre en la tarjeta</label>
          <input class="input" type="text" name="card_name" id="card_name" placeholder="Nombre tal y como aparece en la tarjeta" required>
        </div>

        <div class="form-row">
          <label class="small">Número de tarjeta</label>
          <input class="input" type="tel" name="card_number" id="card_number" inputmode="numeric" placeholder="4242 4242 4242 4242" maxlength="23" required>
        </div>

        <div class="row-inline">
          <div class="col-2">
            <label class="small">Fecha caducidad</label>
            <input class="input" type="month" name="card_exp" id="card_exp" required>
          </div>

          <div style="width:140px;">
            <label class="small">CVC / CVV</label>
            <input class="input" type="text" name="card_cvc" id="card_cvc" maxlength="4" inputmode="numeric" placeholder="123" required>
          </div>
        </div>

        <div style="margin-top:12px;">
          <button class="btn" type="submit">Pagar <?php echo number_format($product['cost_money'], 2); ?> €</button>
        </div>

        <div class="hint">Al confirmar, se simulará el cobro y se te aplicará la mejora.</div>
      </form>
    </div>

  </div>

  <div style="max-width:980px;margin:18px auto;text-align:center">
        <a href="tienda.php" class="btn" style="background:#333;color:#fff;margin-top:12px;">⬅ Volver a la Tienda</a>
  </div>
</div>

<script>
// Formattng & UI niceties
(function(){
  const cardNum = document.getElementById('card_number');
  const cardNumVis = document.getElementById('cardNumberVisual');
  const cardName = document.getElementById('card_name');
  const cardNameVis = document.getElementById('cardNameVisual');
  const cardExp = document.getElementById('card_exp');
  const cardExpVis = document.getElementById('cardExpVisual');
  const cardCvc = document.getElementById('card_cvc');

  // add spaces to card number for readability
  cardNum.addEventListener('input', function(e){
    let v = this.value.replace(/\D/g,'').slice(0,16);
    let parts = [];
    for (let i=0;i<v.length;i+=4) parts.push(v.substr(i,4));
    this.value = parts.join(' ');
    cardNumVis.textContent = this.value ? this.value.padEnd(19,'•') : '•••• •••• •••• ••••';
  });

  cardName.addEventListener('input', function(){ 
    cardNameVis.textContent = this.value ? this.value.toUpperCase() : 'NOMBRE APELLIDOS';
  });

  cardExp.addEventListener('change', function(){
    if (!this.value) { cardExpVis.textContent = 'MM/AA'; return; }
    // value is YYYY-MM, convert to MM/YY
    const parts = this.value.split('-');
    if (parts.length===2) {
      const mm = parts[1];
      const yy = parts[0].slice(2);
      cardExpVis.textContent = mm + '/' + yy;
    }
  });

  // basic client validation on submit
  document.getElementById('paymentForm').addEventListener('submit', function(e){
    const num = cardNum.value.replace(/\s+/g,'');
    if (num.length < 12) { alert('Número de tarjeta demasiado corto (simulación).'); e.preventDefault(); return; }
    if (!cardName.value.trim()) { alert('Introduce el nombre de la tarjeta.'); e.preventDefault(); return; }
    if (!cardExp.value) { alert('Introduce la fecha de caducidad.'); e.preventDefault(); return; }
    if (cardCvc.value.length < 3) { alert('Introduce un CVC válido.'); e.preventDefault(); return; }
  });

})();
</script>

</body>
<?php include_once "../../comun/chatbot.php"; ?>
</html>
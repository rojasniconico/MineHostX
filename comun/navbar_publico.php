
<style>
.public-topbar {
  background: #1E1E1E;
  color: #fff;
  padding: 12px 25px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
  position: sticky;
  top: 0;
  z-index: 999;
}
.public-topbar h2 {
  color: #4FC3F7;
  margin: 0;
  font-size: 1.5em;
}
.public-buttons {
  display: flex;
  gap: 10px;
}
.public-buttons a {
  text-decoration: none;
  background: #4FC3F7;
  color: #000;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: bold;
  transition: 0.2s;
}
.public-buttons a:hover {
  background: #82DAFF;
}
</style>

<div class="public-topbar">
  <h2>MineHostX</h2>
  <div class="public-buttons">
    <a href="autenticacion/login.php">Iniciar sesión</a>
    <a href="autenticacion/registro.php">Registrarse</a>
  </div>
</div>

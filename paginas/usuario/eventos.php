<?php
// Mantenemos tus includes de autenticación, DB y comunes
session_start();
require_once "../../db.php";
require_once "../../comun/puntos.php";
include "../../comun/referrals.php";

// Si el usuario no está autenticado, redirigir
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../autenticacion/login.php");
    exit();
}

// ----------------------------------------------------------------------
// 🔄 CÓDIGO PHP PARA CARGAR EVENTOS ACTIVOS DESDE LA BASE DE DATOS
// ----------------------------------------------------------------------

// 1. Consulta para obtener los eventos activos (Añadimos start_at)
$sql_eventos = "
    SELECT 
        id, 
        name, 
        description, 
        ends_at, 
        start_at,       /* <-- NUEVO CAMPO */
        event_type
    FROM events
    WHERE active = 1
    ORDER BY ends_at ASC
";

$result_eventos = mysqli_query($conn, $sql_eventos);
$eventos_db = [];

$ahora = new DateTime();

if ($result_eventos && mysqli_num_rows($result_eventos) > 0) {
    while ($row = mysqli_fetch_assoc($result_eventos)) {
        
        $fecha_inicio = new DateTime($row['start_at']);
        
        // Determinar si el evento está bloqueado (aún no ha comenzado)
        $bloqueado = $fecha_inicio > $ahora;
        
        $eventos_db[] = [
            'id' => $row['id'],
            'titulo' => htmlspecialchars($row['name']),
            'fecha_inicio' => $fecha_inicio->format('d/m/Y H:i'), // Mostrar la fecha de inicio
            'fecha_fin' => (new DateTime($row['ends_at']))->format('d/m/Y'),
            'tipo' => htmlspecialchars($row['event_type'] ?? 'Comunidad'),
            'descripcion' => htmlspecialchars($row['description']),
            'enlace' => 'evento_detalle.php?id=' . $row['id'],
            'bloqueado' => $bloqueado, // Flag para el HTML
        ];
    }
}

$eventos = $eventos_db;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos | MineHostX</title>

    <style>
        /* CSS Base de tu tema (Sin cambios) */
        body {
            background:#121212;
            color:#fff;
            font-family:"Segoe UI";
            margin:0;
            padding:0;
        }

        .main-container {
            max-width:1000px;
            margin:40px auto;
            background:#1E1E1E;
            padding:25px;
            border-radius:16px;
            box-shadow:0 6px 18px rgba(0,0,0,0.5);
            animation:fadeIn .4s ease;
        }

        h2 {
            text-align:center;
            color:#4FC3F7;
        }
        
        .section-header {
            color: #4FC3F7;
            text-align: center;
            margin-bottom: 20px;
        }

        /* --- CSS DEL CARRUSEL --- */
        .contenedor-carrusel {
            max-width: 500px;
            margin: 40px auto;
            /* El padding es clave para dar espacio a las flechas fuera de la tarjeta */
            padding: 0 50px; 
            position: relative;
            background: #191919;
            border-radius: 12px;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .carrusel-vista { overflow: hidden; width: 100%; }
        .carrusel-pista {
            display: flex;
            transition: transform 0.3s ease-in-out;
            gap: 20px;
        }

        .tarjeta-evento {
            flex-basis: calc(100% / 1.15); 
            flex-shrink: 0; 
            height: 650px; 
            box-sizing: border-box; 
            background: #222; 
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
            position: relative;
            display: flex; 
            flex-direction: column;
            justify-content: space-between; 
            z-index: 1; 
            padding: 30px; 
        }

        /* 🔒 Candado y Opacidad para eventos bloqueados */
        .tarjeta-evento.bloqueado {
            opacity: 0.6; 
            border-left: 5px solid #888; 
        }
        
        .tarjeta-evento.bloqueado .evento-candado {
            display: block;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: #E57373; 
        }

        /* Estilos base de tipo */
        .tarjeta-evento.comunidad { border-left: 5px solid #4FC3F7; }
        .tarjeta-evento.empresa { border-left: 5px solid #E57373; }
        .evento-tipo {
            display: inline-block;
            background-color: #333;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .evento-fecha {
            font-style: italic;
            color: #aaa;
            margin-top: 5px;
        }

        /* Estilo para el botón de detalles */
        .btn-ver-evento {
            margin-top: auto; 
            display: block;
            text-align: center;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        
        /* Botón Activo/Bloqueado */
        .btn-ver-evento.activo {
            background: #4FC3F7;
            color: #000;
        }
        .btn-ver-evento.activo:hover {
            background-color: #79D1FF;
        }
        
        .btn-ver-evento.bloqueado {
            background: #333; 
            color: #888; 
            cursor: not-allowed;
            pointer-events: none; 
        }

        /* Flechas del Carrusel */
        .carrusel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7); 
            color: #4FC3F7;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            z-index: 10;
            font-size: 1.5em;
            border-radius: 50%;
            line-height: 1;
            transition: background 0.2s;
        }
        
        /* 🚀 Solución: Separar las flechas a los bordes */
        .carrusel-btn.izquierda {
            left: 0; 
        }

        .carrusel-btn.derecha {
            right: 0; 
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

<?php 
include_once "../../comun/navbar.php"; 
?>

<div class="main-container">

    <h2 class="section-header">📆 Próximos Eventos y en Curso</h2>

    <main class="contenedor-carrusel">
        
        <h3>Eventos</h3>
        
        <div class="carrusel-vista">
            <div class="carrusel-pista">
                
                <?php if (empty($eventos)): ?>
                    <p style="text-align:center;">Por el momento no hay eventos programados. ¡Vuelve pronto!</p>
                <?php else: ?>
                    <?php foreach ($eventos as $evento): ?>
                        
                        <div class="tarjeta-evento <?php echo strtolower($evento['tipo']); ?> <?php echo $evento['bloqueado'] ? 'bloqueado' : ''; ?>">
                            
                            <?php if ($evento['bloqueado']): ?>
                                <span class="evento-candado" title="Comienza el <?php echo $evento['fecha_inicio']; ?>">🔒</span>
                            <?php endif; ?>

                            <div class="evento-info">
                                <span class="evento-tipo"><?php echo $evento['tipo']; ?></span>
                                <h3 style="color:#4FC3F7;"><?php echo $evento['titulo']; ?></h3>
                                
                                <p class="evento-fecha">
                                    Inicio: <b><?php echo $evento['fecha_inicio']; ?></b>
                                </p>
                                <p class="evento-fecha">
                                    Finaliza: <?php echo $evento['fecha_fin']; ?>
                                </p>
                                <p><?php echo $evento['descripcion']; ?></p>
                            </div>
                            
                            <a 
                                href="<?php echo $evento['enlace']; ?>" 
                                class="btn-ver-evento <?php echo $evento['bloqueado'] ? 'bloqueado' : 'activo'; ?>"
                            >
                                <?php echo $evento['bloqueado'] ? 'Comienza en: ' . $evento['fecha_inicio'] : 'Ver Detalles del Evento'; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <button class="carrusel-btn izquierda" onclick="moverCarrusel(-1)">&#10094;</button>
        <button class="carrusel-btn derecha" onclick="moverCarrusel(1)">&#10095;</button>

    </main>
</div>

<script>
    // La lógica de JavaScript para el carrusel se mantiene igual
    const pista = document.querySelector('.carrusel-pista');
    const eventos = document.querySelectorAll('.tarjeta-evento');
    const totalEventos = eventos.length;
    let indiceActual = 0; 
    let anchoDesplazamiento = 0;

    function calcularAnchoDesplazamiento() {
        if (eventos.length > 0) {
            const anchoTarjeta = eventos[0].offsetWidth;
            const estiloPista = window.getComputedStyle(pista);
            const gap = parseFloat(estiloPista.gap) || 0;
            anchoDesplazamiento = anchoTarjeta + gap;
        }
    }

    function moverCarrusel(direccion) {
        if (totalEventos === 0) return;
        let nuevoIndice = indiceActual + direccion;

        if (nuevoIndice >= totalEventos) {
            nuevoIndice = 0; 
        } else if (nuevoIndice < 0) {
            nuevoIndice = totalEventos - 1; 
        }
        
        indiceActual = nuevoIndice;

        const desplazamiento = indiceActual * -anchoDesplazamiento;
        pista.style.transform = `translateX(${desplazamiento}px)`;
    }

    window.addEventListener('resize', calcularAnchoDesplazamiento);
    
    setTimeout(() => {
        calcularAnchoDesplazamiento();
        moverCarrusel(0); 
    }, 100);

</script>
<footer>
  © <?= date("Y") ?> Comunidad MineHostX — Unidos por los bloques.  
  <br><a href="../../index.php">Volver a inicio</a>
</footer>

<?php include "../../comun/chatbot.php"; ?>

</body>
</html>
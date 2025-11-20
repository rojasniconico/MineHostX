<?php
// api/chatbot.php
header('Content-Type: application/json; charset=utf-8');

// Permitir solicitudes desde cualquier origen (CORS) para pruebas
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener el mensaje enviado
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$message) {
    echo json_encode(['reply' => 'Por favor, escribe algo para que pueda ayudarte.']);
    exit;
}

// Función de respuestas automáticas (fallback inteligente)
function getBotReply($text) {
    $text = strtolower($text);

    // Saludos
    if (preg_match('/hola|buenas|buen día|buenas tardes/', $text)) {
        return "**¡Hola!** 👋 Soy el asistente de **MineHostX**.\n¿En qué puedo ayudarte hoy?";
    }

    // Crear servidor
    if (preg_match('/crear servidor|nuevo servidor|panel/', $text)) {
        return "Para crear un servidor ve a **Panel** (panel.php) y pulsa **➕ Crear nuevo servidor**.";
    }

    // Precios y planes
    if (preg_match('/precio|plan|hosting/', $text)) {
        return "Nuestros planes de hosting se adaptan a tus necesidades. Puedes consultar los detalles en **Planes** dentro del Panel de Control.";
    }

    // Soporte
    if (preg_match('/soporte|ayuda|problema/', $text)) {
        return "Si tienes algún problema, puedes contactar con nuestro soporte: **soporte@minehostx.local**";
    }

    // Preguntas generales
    return "No tengo una respuesta automática para eso todavía. Por favor, contacta con soporte: **soporte@minehostx.local**";
}

// Obtener la respuesta
$reply = getBotReply($message);

// Devolver JSON
echo json_encode(['reply' => $reply]);

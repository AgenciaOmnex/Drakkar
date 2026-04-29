<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variables de entorno requeridas en el servidor (hPanel → PHP Options):
//   N8N_WEBHOOK_URL  = https://agencia-n8n.oyvucf.easypanel.host/webhook/drakkar-chat
//   N8N_CHAT_TOKEN   = drakkar-chat-2025
//   ALLOWED_ORIGIN   = https://drakkarbox.com   (opcional, por defecto el de abajo)
$WEBHOOK        = getenv('N8N_WEBHOOK_URL')  ?: 'https://agencia-n8n.oyvucf.easypanel.host/webhook/drakkar-chat';
$TOKEN          = getenv('N8N_CHAT_TOKEN')   ?: 'drakkar-chat-2025';
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGIN')   ?: 'https://drakkarbox.com';
// ────────────────────────────────────────────────────────────────────

// ── Validación de Origin/Referer ─────────────────────────────────────
// Permite peticiones del propio dominio y bloquea las de terceros.
// Peticiones sin Origin ni Referer (curl, server-to-server) pasan: son
// legítimas en desarrollo local o pruebas con herramientas CLI.
$origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($origin !== '' && strpos($origin, $ALLOWED_ORIGIN) !== 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
if ($origin === '' && $referer !== '' && strpos($referer, $ALLOWED_ORIGIN) !== 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ── Rate limiting (20 peticiones / 60 s por IP) ──────────────────────
function checkRateLimit(int $limit = 20, int $window = 60): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_chat_' . md5($safeIp) . '.json';
    $now    = time();
    $data   = ['ts' => $now, 'count' => 0];

    if (file_exists($file)) {
        $saved = json_decode((string) file_get_contents($file), true);
        if (is_array($saved) && ($now - ($saved['ts'] ?? 0)) < $window) {
            $data = $saved;
        }
    }

    $data['count']++;
    @file_put_contents($file, json_encode($data));

    if ($data['count'] > $limit) {
        http_response_code(429);
        echo json_encode(['reply' => 'Demasiadas peticiones. Espera un momento e inténtalo de nuevo.', 'suggestions' => []]);
        exit;
    }
}
checkRateLimit();

// ── Método ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !isset($input['message']) || !is_string($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

// ── Sanitizar inputs ─────────────────────────────────────────────────
$message = substr(strip_tags($input['message']), 0, 500);
$convId  = isset($input['conversationId']) && is_string($input['conversationId'])
    ? substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['conversationId']), 0, 64)
    : '';

$history = [];
if (isset($input['history']) && is_array($input['history'])) {
    foreach (array_slice($input['history'], -6) as $entry) {
        if (
            isset($entry['role'], $entry['content']) &&
            is_string($entry['role']) &&
            is_string($entry['content']) &&
            in_array($entry['role'], ['user', 'assistant'], true)
        ) {
            $history[] = [
                'role'    => $entry['role'],
                'content' => substr(strip_tags($entry['content']), 0, 500),
            ];
        }
    }
}

// ── Llamada al webhook ────────────────────────────────────────────────
$payload = json_encode([
    'message'        => $message,
    'conversationId' => $convId,
    'history'        => $history,
]);

$ctx = stream_context_create([
    'http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nx-chat-token: {$TOKEN}\r\n",
        'content'       => $payload,
        'timeout'       => 15,
        'ignore_errors' => true,
    ],
]);

$response = @file_get_contents($WEBHOOK, false, $ctx);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}

$decoded = json_decode($response, true);
if (!$decoded || !isset($decoded['reply']) || !is_string($decoded['reply'])) {
    http_response_code(502);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}

$suggestions = isset($decoded['suggestions']) && is_array($decoded['suggestions'])
    ? array_values(array_filter($decoded['suggestions'], 'is_string'))
    : [];

echo json_encode(['reply' => $decoded['reply'], 'suggestions' => $suggestions]);

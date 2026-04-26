<?php
header('Content-Type: application/json; charset=utf-8');

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

// Sanitize inputs
$message = substr(strip_tags($input['message']), 0, 500);
$convId  = isset($input['conversationId']) && is_string($input['conversationId'])
    ? substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', $input['conversationId']), 0, 64)
    : '';

// Sanitize history
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

// ── Configuración (solo en servidor, nunca en frontend) ─────
$WEBHOOK = 'https://agencia-n8n.oyvucf.easypanel.host/webhook/drakkar-chat';
$TOKEN   = 'drakkar-chat-2025';
// ───────────────────────────────────────────────────────────

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

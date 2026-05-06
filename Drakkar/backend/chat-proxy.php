<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variables de entorno requeridas en el servidor (hPanel → PHP Options):
//   N8N_WEBHOOK_URL  (requerida)
//   N8N_CHAT_TOKEN   (requerida)
//   ALLOWED_ORIGINS  (opcional, lista separada por comas — expande www automáticamente)
//   ALLOWED_ORIGIN   (alias de ALLOWED_ORIGINS, una sola entrada)
$WEBHOOK        = getenv('N8N_WEBHOOK_URL') ?: ($_ENV['N8N_WEBHOOK_URL'] ?? ($_SERVER['N8N_WEBHOOK_URL'] ?? ''));
$TOKEN          = getenv('N8N_CHAT_TOKEN')  ?: ($_ENV['N8N_CHAT_TOKEN']  ?? ($_SERVER['N8N_CHAT_TOKEN']  ?? ''));
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGINS') ?: (getenv('ALLOWED_ORIGIN') ?: ($_ENV['ALLOWED_ORIGIN']     ?? ($_SERVER['ALLOWED_ORIGIN'] ?? 'https://drakkarbox.com')));

if (!$WEBHOOK || !$TOKEN) {
    http_response_code(500);
    error_log('[drakkar:chat] Variables de entorno N8N no configuradas');
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}
// ────────────────────────────────────────────────────────────────────

// ── Validación de Origin/Referer ─────────────────────────────────────
// Normaliza origen a scheme://host para comparación exacta.
// Expande www ↔ no-www automáticamente.
// Peticiones sin Origin ni Referer se bloquean: el chat es siempre
// llamado desde el navegador (fetch), que siempre envía Origin en POST.
function normalizeOrigin(string $origin): string {
    $origin = trim($origin);
    if ($origin === '') {
        return '';
    }
    $parts = parse_url($origin);
    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return rtrim($origin, '/');
    }
    $scheme = strtolower($parts['scheme']);
    $host   = strtolower($parts['host']);
    $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
    return $scheme . '://' . $host . $port;
}

function allowedOrigins(string $defaultOrigin): array {
    $configured = getenv('ALLOWED_ORIGINS') ?: getenv('ALLOWED_ORIGIN') ?: $defaultOrigin;
    $items = array_filter(array_map('trim', explode(',', $configured)));
    if (!$items) {
        $items = [$defaultOrigin];
    }

    $allowed = [];
    foreach ($items as $item) {
        $origin = normalizeOrigin($item);
        if ($origin === '') {
            continue;
        }
        $allowed[] = $origin;

        $parts = parse_url($origin);
        if ($parts && !empty($parts['scheme']) && !empty($parts['host'])) {
            $scheme = strtolower($parts['scheme']);
            $host   = strtolower($parts['host']);
            $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

            if (strpos($host, 'www.') === 0) {
                $allowed[] = $scheme . '://' . substr($host, 4) . $port;
            } else {
                $allowed[] = $scheme . '://www.' . $host . $port;
            }
        }
    }

    return array_values(array_unique($allowed));
}

function isAllowedRequestOrigin(string $allowedDefault): bool {
    $origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $allowed = allowedOrigins($allowedDefault);

    if ($origin !== '') {
        return in_array(normalizeOrigin($origin), $allowed, true);
    }

    if ($referer !== '') {
        return in_array(normalizeOrigin($referer), $allowed, true);
    }

    // Sin Origin ni Referer: bloqueado. fetch() en POST siempre envía Origin;
    // si llega sin él es una petición directa no autorizada.
    return false;
}

if (!isAllowedRequestOrigin($ALLOWED_ORIGIN)) {
    http_response_code(403);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}

// ── Rate limiting (20 peticiones / 60 s por IP) ──────────────────────
// flock() elimina la race condition. Fail-open con error_log si /tmp no
// es escribible para no romper el chat.
function checkRateLimit(int $limit = 20, int $window = 60): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_chat_' . md5($safeIp) . '.json';
    $now    = time();

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        error_log('[drakkar:chat] Rate limit: no se pudo abrir archivo en /tmp');
        return;
    }

    flock($fp, LOCK_EX);
    rewind($fp);
    $content = stream_get_contents($fp);
    $data    = ['ts' => $now, 'count' => 0];
    if ($content !== false && $content !== '') {
        $saved = json_decode($content, true);
        if (is_array($saved) && ($now - ($saved['ts'] ?? 0)) < $window) {
            $data = $saved;
        }
    }

    $data['count']++;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($data['count'] > $limit) {
        $retryAfter = max(1, $window - ($now - ($data['ts'] ?? $now)));
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        echo json_encode(['reply' => 'Demasiadas peticiones. Espera un momento e inténtalo de nuevo.', 'suggestions' => []]);
        exit;
    }
}
checkRateLimit();

// ── Método ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error.', 'suggestions' => []]);
    exit;
}

// ── Tamaño de body ────────────────────────────────────────────────────
// Límite de 64 KB: muy por encima de cualquier mensaje legítimo.
$raw = file_get_contents('php://input', false, null, 0, 65536);
if ($raw === false || strlen($raw) === 0) {
    http_response_code(400);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error.', 'suggestions' => []]);
    exit;
}

$input = json_decode($raw, true);

if (!$input || !isset($input['message']) || !is_string($input['message'])) {
    http_response_code(400);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error.', 'suggestions' => []]);
    exit;
}

// ── Sanitizar inputs ─────────────────────────────────────────────────
$message = trim(strip_tags($input['message']));
$message = substr($message, 0, 500);

if ($message === '') {
    http_response_code(400);
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error.', 'suggestions' => []]);
    exit;
}

$convId = isset($input['conversationId']) && is_string($input['conversationId'])
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
                'content' => substr(trim(strip_tags($entry['content'])), 0, 500),
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
    error_log('[drakkar:chat] No se pudo conectar con el webhook n8n');
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}

$decoded = json_decode($response, true);

// n8n puede devolver distintos formatos según el nodo usado:
// {reply:"..."}, {output:"..."}, {text:"..."}, {message:"..."} o incluso
// un array con el primer item. Normalizamos sin exponer detalles técnicos.
// Compatibilidad PHP 7.4+: no usamos array_is_list() (requiere PHP 8.1).
if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0]) && !array_key_exists('reply', $decoded)) {
    $decoded = $decoded[0];
}

$reply = '';
if (is_array($decoded)) {
    foreach (['reply', 'output', 'text', 'message', 'response'] as $key) {
        if (isset($decoded[$key]) && is_string($decoded[$key]) && trim($decoded[$key]) !== '') {
            $reply = $decoded[$key];
            break;
        }
    }
} elseif (is_string($decoded) && trim($decoded) !== '') {
    $reply = $decoded;
}

if ($reply === '') {
    http_response_code(502);
    error_log('[drakkar:chat] n8n devolvió respuesta sin campo de texto reconocido');
    echo json_encode(['reply' => 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: 604 95 57 06', 'suggestions' => []]);
    exit;
}

$suggestions = isset($decoded['suggestions']) && is_array($decoded['suggestions'])
    ? array_values(array_filter($decoded['suggestions'], 'is_string'))
    : [];

echo json_encode(['reply' => $reply, 'suggestions' => $suggestions]);

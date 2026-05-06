<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variables de entorno requeridas en el servidor (hPanel → PHP Options):
//   GOOGLE_PLACES_API_KEY (requerida)
//   ALLOWED_ORIGINS       (opcional, lista separada por comas — expande www automáticamente)
//   ALLOWED_ORIGIN        (alias de ALLOWED_ORIGINS, una sola entrada)
$API_KEY        = getenv('GOOGLE_PLACES_API_KEY') ?: ($_ENV['GOOGLE_PLACES_API_KEY'] ?? ($_SERVER['GOOGLE_PLACES_API_KEY'] ?? ''));
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGINS')        ?: (getenv('ALLOWED_ORIGIN')       ?: ($_ENV['ALLOWED_ORIGIN']          ?? ($_SERVER['ALLOWED_ORIGIN'] ?? 'https://drakkarbox.com')));
$PLACE_ID       = 'ChIJwxZvZeDREQ0RBA9Ggrk1s_c'; // Drakkar Box — C. la Gravera, 8, Huelva
$CACHE          = __DIR__ . '/reviews-cache.json';
$CACHE_TTL      = 86400; // 24 horas
// ────────────────────────────────────────────────────────────────────

// ── Método ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'ERROR', 'error_message' => 'Method not allowed']);
    exit;
}

// ── Validación de Origin/Referer ─────────────────────────────────────
// Normaliza origen a scheme://host para comparación exacta.
// Expande www ↔ no-www automáticamente.
// Peticiones sin Origin ni Referer se bloquean. Para este endpoint GET,
// fetch() same-origin no envía Origin pero sí Referer (Referrer-Policy:
// strict-origin-when-cross-origin en .htaccess). Si se necesitan pruebas
// CLI, añadir: -H "Origin: https://drakkarbox.com"
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

    // Sin Origin ni Referer: bloqueado. Los navegadores siempre envían
    // Referer en fetch() same-origin con nuestra Referrer-Policy activa.
    return false;
}

if (!isAllowedRequestOrigin($ALLOWED_ORIGIN)) {
    http_response_code(403);
    echo json_encode(['status' => 'ERROR', 'error_message' => 'Forbidden']);
    exit;
}

// ── Rate limiting (30 peticiones / 60 s por IP) ──────────────────────
// flock() elimina la race condition de las tres operaciones no atómicas
// anteriores. Fail-open con error_log si /tmp no es escribible.
function checkRateLimit(int $limit = 30, int $window = 60): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_reviews_' . md5($safeIp) . '.json';
    $now    = time();

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        error_log('[drakkar:reviews] Rate limit: no se pudo abrir archivo en /tmp');
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
        echo json_encode(['status' => 'ERROR', 'error_message' => 'Too many requests']);
        exit;
    }
}
checkRateLimit();

// ── API key requerida ─────────────────────────────────────────────────
if (!$API_KEY) {
    http_response_code(500);
    error_log('[drakkar:reviews] Variable de entorno GOOGLE_PLACES_API_KEY no configurada');
    echo json_encode(['status' => 'ERROR', 'error_message' => 'Servicio no disponible']);
    exit;
}

// ── Servir caché si existe y es reciente ─────────────────────────────
if (file_exists($CACHE) && (time() - filemtime($CACHE)) < $CACHE_TTL) {
    readfile($CACHE);
    exit;
}

// ── Llamada a Google Places API ───────────────────────────────────────
$url = 'https://maps.googleapis.com/maps/api/place/details/json'
     . '?place_id=' . urlencode($PLACE_ID)
     . '&fields=reviews,rating,user_ratings_total'
     . '&language=es'
     . '&reviews_sort=newest'
     . '&key=' . $API_KEY;

$ctx  = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
$data = @file_get_contents($url, false, $ctx);

if ($data === false) {
    if (file_exists($CACHE)) {
        readfile($CACHE);
    } else {
        error_log('[drakkar:reviews] No se pudo conectar con Google Places API');
        echo json_encode(['status' => 'ERROR', 'error_message' => 'Servicio no disponible']);
    }
    exit;
}

$decoded = json_decode($data, true);

if (!$decoded || ($decoded['status'] ?? '') !== 'OK') {
    if (file_exists($CACHE)) {
        readfile($CACHE);
    } else {
        error_log('[drakkar:reviews] Google Places API respondió con status: ' . ($decoded['status'] ?? 'desconocido'));
        echo json_encode(['status' => 'ERROR', 'error_message' => 'Servicio no disponible']);
    }
    exit;
}

// ── Guardar nueva caché y servir ─────────────────────────────────────
file_put_contents($CACHE, $data);
echo $data;

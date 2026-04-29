<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variables de entorno requeridas en el servidor (hPanel → PHP Options):
//   GOOGLE_PLACES_API_KEY = tu_clave_google
//   ALLOWED_ORIGIN        = https://drakkarbox.com  (opcional)
$API_KEY        = getenv('GOOGLE_PLACES_API_KEY') ?: '';
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGIN')        ?: 'https://drakkarbox.com';
$PLACE_ID       = 'ChIJwxZvZeDREQ0RBA9Ggrk1s_c'; // Drakkar Box — C. la Gravera, 8, Huelva
$CACHE          = __DIR__ . '/reviews-cache.json';
$CACHE_TTL      = 86400; // 24 horas
// ────────────────────────────────────────────────────────────────────

// ── Validación de Origin/Referer ─────────────────────────────────────
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

// ── Rate limiting (30 peticiones / 60 s por IP) ──────────────────────
// Las reseñas se cachean 24h, así que el límite real es muy holgado.
function checkRateLimit(int $limit = 30, int $window = 60): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_reviews_' . md5($safeIp) . '.json';
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
        echo json_encode(['status' => 'ERROR', 'error_message' => 'Too many requests']);
        exit;
    }
}
checkRateLimit();

// ── API key requerida ─────────────────────────────────────────────────
if (!$API_KEY) {
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'error_message' => 'API key no configurada']);
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
        echo json_encode(['status' => 'ERROR', 'error_message' => 'No se pudo conectar con Google']);
    }
    exit;
}

$decoded = json_decode($data, true);

if (!$decoded || ($decoded['status'] ?? '') !== 'OK') {
    if (file_exists($CACHE)) {
        readfile($CACHE);
    } else {
        echo $data;
    }
    exit;
}

// ── Guardar nueva caché y servir ─────────────────────────────────────
file_put_contents($CACHE, $data);
echo $data;

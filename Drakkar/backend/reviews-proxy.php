<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ─────────────────────────────────
// Requiere variable de entorno: GOOGLE_PLACES_API_KEY
// En el servidor: añadir al .env, al panel de hosting, o en php.ini con:
//   SetEnv GOOGLE_PLACES_API_KEY tu_clave_aqui
$API_KEY   = getenv('GOOGLE_PLACES_API_KEY') ?: '';
$PLACE_ID  = 'ChIJwxZvZeDREQ0RBA9Ggrk1s_c'; // Drakkar Box — C. la Gravera, 8, Huelva
$CACHE     = __DIR__ . '/reviews-cache.json';
$CACHE_TTL = 86400;                 // Caché de 24 horas (en segundos)
// ─────────────────────────────────────────────────

if (!$API_KEY) {
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'error_message' => 'API key no configurada']);
    exit;
}

// Servir caché si existe y es reciente
if (file_exists($CACHE) && (time() - filemtime($CACHE)) < $CACHE_TTL) {
    readfile($CACHE);
    exit;
}

$url = 'https://maps.googleapis.com/maps/api/place/details/json'
     . '?place_id=' . urlencode($PLACE_ID)
     . '&fields=reviews,rating,user_ratings_total'
     . '&language=es'
     . '&reviews_sort=newest'
     . '&key=' . $API_KEY;

$ctx  = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
$data = @file_get_contents($url, false, $ctx);

// Si falla la petición, devolver caché antigua si existe
if ($data === false) {
    if (file_exists($CACHE)) {
        readfile($CACHE);
    } else {
        echo json_encode(['status' => 'ERROR', 'error_message' => 'No se pudo conectar con Google']);
    }
    exit;
}

$decoded = json_decode($data, true);

// Si la respuesta de Google no es OK, devolver caché antigua si existe
if (!$decoded || ($decoded['status'] ?? '') !== 'OK') {
    if (file_exists($CACHE)) {
        readfile($CACHE);
    } else {
        echo $data;
    }
    exit;
}

// Guardar nueva caché y servir
file_put_contents($CACHE, $data);
echo $data;

<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ─────────────────────────────────
$API_KEY   = 'AIzaSyBUwDgoAjW1RPvGErvHgYhTAfzPB5v3rAU';
$PLACE_ID  = 'ChIJwxZvZeDREQ0RBA9Ggrk1s_c'; // Drakkar Box — C. la Gravera, 8, Huelva
$CACHE     = __DIR__ . '/reviews-cache.json';
$CACHE_TTL = 86400;                 // Caché de 24 horas (en segundos)
// ─────────────────────────────────────────────────

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

<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variable de entorno requerida en el servidor (hPanel → PHP Options):
//   GOOGLE_SHEETS_URL = https://script.google.com/macros/s/.../exec
//   ALLOWED_ORIGIN    = https://drakkarbox.com  (opcional)
$SHEETS_URL     = getenv('GOOGLE_SHEETS_URL') ?: 'https://script.google.com/macros/s/AKfycby9ycAsai_2Nc-cY3pNZyF9K2y5pPWEb1m0FPgUWs4QF6oIHOHlWwAUUG7MB0Gd_RGk/exec';
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGIN')    ?: 'https://drakkarbox.com';
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

// ── Rate limiting (5 envíos / 10 min por IP) ─────────────────────────
function checkRateLimit(int $limit = 5, int $window = 600): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_form_' . md5($safeIp) . '.json';
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
        echo json_encode(['error' => 'Demasiados intentos. Espera unos minutos e inténtalo de nuevo.']);
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

// ── Parsear body ─────────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

// ── Sanitizar inputs ─────────────────────────────────────────────────
$nombre     = substr(strip_tags($input['nombre']     ?? ''), 0, 100);
$apellidos  = substr(strip_tags($input['apellidos']  ?? ''), 0, 100);
$telefono   = substr(preg_replace('/[^\d\s+\-()]/', '', $input['telefono'] ?? ''), 0, 20);
$correo     = substr(strip_tags($input['correo']     ?? ''), 0, 200);
$tarifa     = substr(strip_tags($input['tarifa']     ?? ''), 0, 100);
$lesion     = substr(strip_tags($input['lesion']     ?? ''), 0, 500);
$enfermedad = substr(strip_tags($input['enfermedad'] ?? ''), 0, 500);
$fecha      = date('d/m/Y'); // Generada en servidor, no se acepta del cliente

// ── Validar campos obligatorios ──────────────────────────────────────
if (!$nombre || !$apellidos || !$telefono || !$correo || !$tarifa) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo electrónico inválido']);
    exit;
}

// ── Reenviar a Google Apps Script ────────────────────────────────────
$payload = json_encode([
    'nombre'     => $nombre,
    'apellidos'  => $apellidos,
    'telefono'   => $telefono,
    'correo'     => $correo,
    'tarifa'     => $tarifa,
    'lesion'     => $lesion,
    'enfermedad' => $enfermedad,
    'fecha'      => $fecha,
]);

$ctx = stream_context_create([
    'http' => [
        'method'          => 'POST',
        'header'          => "Content-Type: text/plain\r\n",
        'content'         => $payload,
        'timeout'         => 15,
        'ignore_errors'   => true,
        'follow_location' => true,
        'max_redirects'   => 5,
    ],
]);

$response = @file_get_contents($SHEETS_URL, false, $ctx);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'No se pudo conectar con Google Sheets. Inténtalo de nuevo o escríbenos por WhatsApp: 604 95 57 06']);
    exit;
}

// Verificar código HTTP de la respuesta final
$headers    = $http_response_header ?? [];
$statusLine = $headers[0] ?? 'HTTP/1.1 200';
preg_match('/HTTP\/\d+\.?\d*\s+(\d+)/', $statusLine, $m);
$statusCode = (int) ($m[1] ?? 200);

if ($statusCode >= 400) {
    http_response_code(502);
    echo json_encode(['error' => 'Error al guardar en Google Sheets (código ' . $statusCode . '). Inténtalo de nuevo o escríbenos por WhatsApp: 604 95 57 06']);
    exit;
}

echo json_encode(['ok' => true]);

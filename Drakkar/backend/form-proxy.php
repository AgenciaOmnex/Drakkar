<?php
header('Content-Type: application/json; charset=utf-8');

// ── Configuración ────────────────────────────────────────────────────
// Variables de entorno requeridas en el servidor (hPanel → PHP Options):
//   GOOGLE_SHEETS_URL (requerida)
//   ALLOWED_ORIGINS   (opcional, lista separada por comas — expande www automáticamente)
//   ALLOWED_ORIGIN    (alias de ALLOWED_ORIGINS, una sola entrada)
$SHEETS_URL     = getenv('GOOGLE_SHEETS_URL') ?: ($_ENV['GOOGLE_SHEETS_URL'] ?? ($_SERVER['GOOGLE_SHEETS_URL'] ?? ''));
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGINS')   ?: (getenv('ALLOWED_ORIGIN')   ?: ($_ENV['ALLOWED_ORIGIN']      ?? ($_SERVER['ALLOWED_ORIGIN'] ?? 'https://drakkarbox.com')));

if (!$SHEETS_URL) {
    http_response_code(500);
    error_log('[drakkar:form] Variable de entorno GOOGLE_SHEETS_URL no configurada');
    echo json_encode(['error' => 'Servicio no disponible']);
    exit;
}
// ────────────────────────────────────────────────────────────────────

// ── Validación de Origin/Referer ─────────────────────────────────────
// Normaliza origen a scheme://host para comparación exacta.
// Expande www ↔ no-www automáticamente.
// Peticiones sin Origin ni Referer se bloquean: el formulario es siempre
// llamado desde el navegador (fetch POST), que siempre envía Origin.
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
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ── Rate limiting (5 envíos / 10 min por IP) ─────────────────────────
// flock() elimina la race condition. Fail-open con error_log si /tmp no
// es escribible para no romper el formulario.
function checkRateLimit(int $limit = 5, int $window = 600): void {
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $safeIp = preg_replace('/[^a-zA-Z0-9:.]/', '', $ip);
    $file   = sys_get_temp_dir() . '/dk_rl_form_' . md5($safeIp) . '.json';
    $now    = time();

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        error_log('[drakkar:form] Rate limit IP: no se pudo abrir archivo en /tmp');
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
        echo json_encode(['error' => 'Demasiados intentos. Espera unos minutos e inténtalo de nuevo.']);
        exit;
    }
}
checkRateLimit();

// ── Rate limiting por email (3 envíos / hora) ─────────────────────────
// Segunda capa: bloquea al mismo usuario aunque cambie de IP.
function checkEmailRateLimit(string $email, int $limit = 3, int $window = 3600): void {
    $hash = md5(strtolower(trim($email)));
    $file = sys_get_temp_dir() . '/dk_rl_email_' . $hash . '.json';
    $now  = time();

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        error_log('[drakkar:form] Rate limit email: no se pudo abrir archivo en /tmp');
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
        echo json_encode(['error' => 'Demasiados intentos. Espera unos minutos e inténtalo de nuevo.']);
        exit;
    }
}

// ── Método ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── Tamaño de body ────────────────────────────────────────────────────
// Límite de 32 KB: muy por encima de cualquier envío legítimo del formulario.
$raw = file_get_contents('php://input', false, null, 0, 32768);
if ($raw === false || strlen($raw) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

$input = json_decode($raw, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

// ── Honeypot anti-bot ─────────────────────────────────────────────────
// El campo hp está oculto vía CSS: los bots lo rellenan, los humanos no.
// Respuesta silenciosa para no revelar el mecanismo al bot.
if (($input['hp'] ?? '') !== '') {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

// ── Sanitizar inputs ─────────────────────────────────────────────────
$nombre     = substr(trim(strip_tags($input['nombre']     ?? '')), 0, 100);
$apellidos  = substr(trim(strip_tags($input['apellidos']  ?? '')), 0, 100);
$telefono   = substr(preg_replace('/[^\d\s+\-()]/', '', $input['telefono'] ?? ''), 0, 20);
$correo     = substr(trim(strip_tags($input['correo']     ?? '')), 0, 200);
$tarifa     = trim(strip_tags($input['tarifa'] ?? ''));
$lesion     = substr(trim(strip_tags($input['lesion']     ?? '')), 0, 500);
$enfermedad = substr(trim(strip_tags($input['enfermedad'] ?? '')), 0, 500);
$fecha      = date('d/m/Y'); // Generada en servidor, no se acepta del cliente

// ── Validar campos obligatorios ──────────────────────────────────────
if ($nombre === '' || $apellidos === '' || $correo === '' || $tarifa === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

// Teléfono: mínimo 6 dígitos (cubre formatos internacionales cortos)
$telefonoDigitos = preg_replace('/[^\d]/', '', $telefono);
if (strlen($telefonoDigitos) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Teléfono inválido']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo electrónico inválido']);
    exit;
}
checkEmailRateLimit($correo);

// ── Validar tarifa contra lista permitida ────────────────────────────
// Solo se aceptan las tarifas que aparecen en el selector del formulario.
$TARIFAS_PERMITIDAS = [
    'Drakkar Basic — 55€/mes (3 clases/semana)',
    'Open Box — 60€/mes (Open ilimitado)',
    'Drakkar WOD — 65€/mes (6 clases/semana)',
    'Drakkar Premium — 70€/mes (Acceso ilimitado)',
    'Bono 5 clases — 35€',
    'Bono 10 clases — 65€',
];
if (!in_array($tarifa, $TARIFAS_PERMITIDAS, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tarifa no válida']);
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
    error_log('[drakkar:form] No se pudo conectar con Google Sheets');
    echo json_encode(['error' => 'No se pudo procesar la inscripción. Inténtalo de nuevo o escríbenos por WhatsApp: 604 95 57 06']);
    exit;
}

// Verificar código HTTP de la respuesta de Google Apps Script
$headers    = $http_response_header ?? [];
$statusLine = $headers[0] ?? 'HTTP/1.1 200';
preg_match('/HTTP\/\d+\.?\d*\s+(\d+)/', $statusLine, $m);
$statusCode = (int) ($m[1] ?? 200);

if ($statusCode >= 400) {
    http_response_code(502);
    error_log('[drakkar:form] Google Sheets respondió con error HTTP ' . $statusCode);
    echo json_encode(['error' => 'No se pudo procesar la inscripción. Inténtalo de nuevo o escríbenos por WhatsApp: 604 95 57 06']);
    exit;
}

echo json_encode(['ok' => true]);

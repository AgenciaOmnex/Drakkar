# Drakkar Box — Guía de Despliegue

## Hosting recomendado: Hostinger (shared hosting con PHP)

El proyecto combina archivos estáticos (HTML, CSS, JS, imágenes) con dos
scripts PHP en `/backend/`. Esto hace que el único hosting que funciona
**al 100%** sea un servidor con soporte PHP, como Hostinger.

---

## Arquitectura del proyecto

| Capa | Archivos | Requiere |
|------|----------|----------|
| Frontend estático | `index.html`, `assets/` | Cualquier hosting |
| Proxy del chat | `backend/chat-proxy.php` | PHP en el servidor |
| Proxy de reseñas | `backend/reviews-proxy.php` | PHP + variable de entorno |
| Formulario de alta | `backend/form-proxy.php` → Google Sheets | PHP en el servidor |

---

## Opciones de hosting

### ✅ Hostinger (recomendado)

- PHP disponible en todos los planes (≥ Business).
- `mod_headers` activo → el `.htaccess` raíz aplica headers de seguridad.
- Subir archivos vía hPanel File Manager o FTP/SFTP.
- SSL gratuito con Let's Encrypt desde hPanel → activar y luego
  descomentar la redirección HTTP→HTTPS en `.htaccess`.

**Funcionalidades disponibles:** todo.

---

### ⚠️ Netlify / Cloudflare Pages (estático parcial)

Sirven archivos estáticos con CDN global y SSL automático, pero **no
ejecutan PHP**. El archivo `_headers` aplica los headers de seguridad.

| Funcionalidad | Estado |
|---------------|--------|
| Web estática | ✅ Funciona |
| Formulario de alta | ❌ No funciona (requiere `form-proxy.php`) |
| Chat Drakkar (chatbot) | ❌ No funciona (requiere `chat-proxy.php`) |
| Reseñas de Google | ❌ No funciona (muestra reseñas estáticas del JS) |

Para que el chat y las reseñas funcionen en estas plataformas habría
que reescribir los proxies PHP como **Netlify Functions** o
**Cloudflare Workers** (Node.js). Tarea fuera del scope actual.

---

### ❌ GitHub Pages (no recomendado)

- No soporta PHP.
- No permite cabeceras HTTP personalizadas (sin `_headers`).
- No hay redirección HTTP→HTTPS configurable fácilmente.
- Las mismas limitaciones que Netlify pero sin CDN ni `_headers`.

**Conclusión:** GitHub Pages solo sirve para proyectos 100% estáticos
sin necesidad de headers de seguridad personalizados.

---

## Variables de entorno necesarias

**En Hostinger (hPanel):** hPanel → Hosting → tu dominio → PHP Configuration → Environment Variables (o añadir `SetEnv` al `php.ini` del dominio).

| Variable | Archivo que la usa | Sin variable |
|----------|--------------------|--------------|
| `GOOGLE_PLACES_API_KEY` | `reviews-proxy.php` | Error 500 → fallback a reseñas estáticas |
| `GOOGLE_SHEETS_URL` | `form-proxy.php` | Error 500 → servicio no disponible |
| `N8N_WEBHOOK_URL` | `chat-proxy.php` | Error 500 → servicio no disponible |
| `N8N_CHAT_TOKEN` | `chat-proxy.php` | Error 500 → servicio no disponible |
| `ALLOWED_ORIGINS` | los tres proxies | Default `https://drakkarbox.com` (correcto) |

```
GOOGLE_PLACES_API_KEY=tu_clave_google_places
GOOGLE_SHEETS_URL=https://script.google.com/macros/s/…/exec
N8N_WEBHOOK_URL=https://tu-n8n.ejemplo.com/webhook/drakkar-chat
N8N_CHAT_TOKEN=tu_token_secreto_aqui
ALLOWED_ORIGINS=https://drakkarbox.com,https://www.drakkarbox.com
```

> **Importante:** estas variables son obligatorias. Sin ellas los proxies PHP devuelven
> error 500. Configúralas siempre en hPanel antes de activar el sitio.
>
> `ALLOWED_ORIGINS` acepta una lista de dominios separados por comas. El código expande
> automáticamente www ↔ no-www, por lo que `https://drakkarbox.com` ya cubre
> `https://www.drakkarbox.com`. Incluir ambos en la lista es redundante pero inofensivo.

---

## Cómo probar el formulario de alta

El formulario envía un POST al proxy PHP (`backend/form-proxy.php`), que lo
reenvía servidor a servidor a Google Apps Script → Google Sheets.

1. Abrir la web en el dominio final.
2. Ir a la sección "Inscripción".
3. Rellenar todos los campos obligatorios y marcar el checkbox RGPD.
4. Enviar y verificar que aparece el mensaje de éxito.
5. Comprobar en Google Sheets que el registro llegó correctamente.

**Prueba rápida con curl (contra el proxy PHP):**
```bash
curl -X POST https://drakkarbox.com/backend/form-proxy.php \
  -H "Content-Type: application/json" \
  -H "Origin: https://drakkarbox.com" \
  -d '{"nombre":"Test","apellidos":"Prueba","telefono":"600000000","correo":"test@test.com","tarifa":"Bono 5 clases — 35€","lesion":"","enfermedad":""}'
```

Respuesta esperada: `{"ok":true}`

---

## Cómo probar el chat

1. Abrir la web en el servidor con PHP activo.
2. Hacer clic en el botón flotante del chat (esquina inferior derecha).
3. Enviar un mensaje y verificar que el bot responde.
4. Si el bot responde con el error de WhatsApp: revisar que el webhook
   de n8n está activo y accesible, y que `$TOKEN` coincide.

**Prueba directa al proxy:**
```bash
curl -X POST https://drakkarbox.com/backend/chat-proxy.php \
  -H "Content-Type: application/json" \
  -d '{"message":"Hola","conversationId":"test123","history":[]}'
```

Respuesta esperada: `{"reply":"...","suggestions":[...]}`

---

## Cómo probar las reseñas de Google

1. Configurar `GOOGLE_PLACES_API_KEY` en el servidor.
2. Abrir la web y bajar hasta la sección "Reseñas".
3. Si las reseñas muestran nombres reales (no los del array `REVIEWS`
   en `script.js`), el proxy funciona.
4. El proxy guarda caché en `backend/reviews-cache.json` (24h).
   Para forzar actualización: borrar ese archivo en el servidor.

**Prueba directa al proxy:**
```bash
curl https://drakkarbox.com/backend/reviews-proxy.php
```

Respuesta esperada: JSON con `"status":"OK"` y array de reseñas.

---

## Qué requiere backend PHP

| Funcionalidad | Archivo PHP | Sin PHP |
|---------------|-------------|---------|
| Chatbot Drakkar | `backend/chat-proxy.php` | Error silencioso en UI |
| Reseñas Google | `backend/reviews-proxy.php` | Fallback a reseñas estáticas |
| Formulario de alta | `backend/form-proxy.php` | Error al enviar |

---

## Headers de seguridad aplicados

| Header | Valor | Para qué sirve |
|--------|-------|----------------|
| `X-Content-Type-Options` | `nosniff` | Evita MIME-sniffing |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | No filtra URL completa a terceros |
| `X-Frame-Options` | `DENY` | Evita clickjacking |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Bloquea sensores por defecto |
| `X-XSS-Protection` | `0` | Desactiva auditor XSS obsoleto |
| `Content-Security-Policy` | ver `.htaccess` | Controla recursos cargables |

**Nota sobre CSP y `unsafe-inline`:** el HTML contiene muchos atributos
`style=""` inline que hacen necesario `unsafe-inline` en `style-src`.
Esto permite que CSS inline funcione pero reduce la protección XSS en estilos.
Para eliminar esta excepción habría que mover todos los estilos inline a
clases CSS (tarea de refactoring futura).

---

## Checklist antes de publicar

### Dominio y SSL
- [ ] Dominio `drakkarbox.com` apuntando al servidor de Hostinger (DNS)
- [ ] Certificado SSL activado en hPanel (Let's Encrypt)
- [ ] Redireccion HTTP→HTTPS descomentada en `.htaccess`
- [ ] Verificar que `https://drakkarbox.com/` carga sin errores de SSL

### Archivos subidos
- [ ] Toda la carpeta del proyecto subida (excepto `/assets/img/originals/`)
- [ ] `.htaccess` raíz presente y activo
- [ ] `backend/.htaccess` presente
- [ ] Permisos de `backend/` a 755 (directorio) y archivos PHP a 644

### Variables de entorno
- [ ] `GOOGLE_PLACES_API_KEY` configurada en hPanel
- [ ] `GOOGLE_SHEETS_URL` configurada en hPanel
- [ ] `N8N_WEBHOOK_URL` configurada en hPanel
- [ ] `N8N_CHAT_TOKEN` configurada en hPanel
- [ ] `ALLOWED_ORIGIN=https://drakkarbox.com` configurada en hPanel
- [ ] Verificar que `backend/reviews-proxy.php` responde con `status: OK`

### Backend y funcionalidades
- [ ] Chat responde correctamente (webhook n8n activo)
- [ ] Reseñas cargan desde Google (no solo las estáticas)
- [ ] Formulario de alta llega a Google Sheets
- [ ] `backend/reviews-cache.json` NO accesible vía URL directa

### SEO y técnico
- [ ] `sitemap.xml` enviado a Google Search Console
- [ ] Schema.org validado en schema.org/SchemaValidator con URL final
- [ ] `canonical` apunta a `https://drakkarbox.com/` (ya configurado)
- [ ] OG image accesible en `https://drakkarbox.com/assets/img/og%20image.jpg`

### Seguridad
- [ ] Headers de seguridad verificados en securityheaders.com
- [ ] `backend/` no muestra listado de directorio (404 o 403 esperado)
- [ ] No hay errores en consola del navegador (F12)

### Rendimiento
- [ ] PageSpeed Insights > 85 móvil (lighthouse)
- [ ] Imágenes WebP cargando correctamente
- [ ] Caché de assets activa (verificar cabecera `Cache-Control` en DevTools)

---

## Riesgos pendientes

| Riesgo | Impacto | Estado |
|--------|---------|--------|
| Secretos hardcodeados como fallback en PHP | — | ✅ Eliminados: env vars obligatorias |
| `unsafe-inline` en CSP style-src | Medio | Requiere refactor de atributos style="" a clases CSS |
| Rate limiting en `/tmp` (file-based por IP) | Bajo | Implementado en los 3 proxies (5 req / 10 min) |
| Videos sin `poster` attribute | Resuelto | ✅ Posters añadidos (lolo.webp, riki.webp, blas.webp) |
| `backend/` accesible si no hay `.htaccess` | Alto | `.htaccess` incluido en el proyecto |
| HSTS no activado | Informativo | Activar manualmente semanas después de SSL estable |
| Chatbot bloqueado si dominio es www y ALLOWED_ORIGIN es sin www | Alto | ✅ Resuelto: multi-origen con expansión www automática |
| Chatbot roto si n8n cambia formato de respuesta | Medio | ✅ Resuelto: proxy normaliza reply/output/text/message/response |

// config/security.php
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; font-src 'self' https://cdn.tailwindcss.com;");

// Prevenir cache em páginas sensíveis
if (strpos($_SERVER['REQUEST_URI'], '/admin/') === 0) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
}

// Proteção básica contra clickjacking
header('X-Frame-Options: DENY');

// Desabilitar divulgação de versão do PHP
header_remove('X-Powered-By');

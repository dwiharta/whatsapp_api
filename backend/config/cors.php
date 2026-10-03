<?php
// ============================================================
// CORS + SESSION UNTUK FRONTEND GITHUB PAGES
// ============================================================
// GANTI nilai ini dengan alamat GitHub Pages kamu.
$allowedOrigins = [
    'https://USERNAME.github.io'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Cookie session harus cross-site agar GitHub Pages dapat memakai session PHP.
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'None');

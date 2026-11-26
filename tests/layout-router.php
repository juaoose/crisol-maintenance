<?php
// Test-only server: render the real plugin template with its WordPress URL helpers.
define('ABSPATH', dirname(__DIR__) . '/');
function esc_url($url) { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
function plugins_url($path, $file) { return '/plugin/' . $path; }

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$plugin = dirname(__DIR__) . '/el-crisol-mantenimiento/';
if ($path === '/') {
    require $plugin . 'template.php';
    return;
}
$assets = array(
    '/plugin/styles.css' => array('styles.css', 'text/css'),
    '/plugin/assets/logo-transparent.png' => array('assets/logo-transparent.png', 'image/png'),
);
if (isset($assets[$path])) {
    header('Content-Type: ' . $assets[$path][1]);
    readfile($plugin . $assets[$path][0]);
    return;
}
http_response_code(404);
echo 'Not found';

<?php
declare(strict_types=1);
require_once __DIR__ . '/autenticacion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}
exigirSesion();
exigirToken();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
responderJson(['ok' => true, 'mensaje' => 'Sesión cerrada.']);

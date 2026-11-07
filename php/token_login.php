<?php
declare(strict_types=1);
require_once __DIR__ . '/autenticacion.php';
if (!empty($_SESSION['usuario_id'])) {
    responderJson(['ok' => true, 'csrf_token' => crearToken()]);
}
if (empty($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}
responderJson(['ok' => true, 'csrf_token' => $_SESSION['login_csrf']]);

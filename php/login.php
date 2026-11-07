<?php
declare(strict_types=1);
require_once __DIR__ . '/autenticacion.php';
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['login_csrf']) || !hash_equals($_SESSION['login_csrf'], $token)) {
    responderJson(['ok' => false, 'mensaje' => 'Token de seguridad inválido. Recarga el formulario.'], 403);
}

$usuario = trim((string)($_POST['usuario'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    responderJson(['ok' => false, 'mensaje' => 'Escribe el usuario y la contraseña.'], 422);
}

$stmt = $conexion->prepare('SELECT id, usuario, password FROM usuarios WHERE usuario = :usuario LIMIT 1');
$stmt->execute(['usuario' => $usuario]);
$registro = $stmt->fetch();

if (!$registro || !password_verify($password, $registro['password'])) {
    responderJson(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.'], 401);
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int)$registro['id'];
$_SESSION['usuario'] = $registro['usuario'];
unset($_SESSION['login_csrf']);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

responderJson(['ok' => true, 'mensaje' => 'Acceso correcto.', 'csrf_token' => $_SESSION['csrf_token']]);

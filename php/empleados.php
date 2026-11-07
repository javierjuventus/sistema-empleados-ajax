<?php
declare(strict_types=1);
require_once __DIR__ . '/autenticacion.php';
require_once __DIR__ . '/conexion.php';

exigirSesion();

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $porPagina = (int)($_GET['por_pagina'] ?? 5);
    $porPagina = in_array($porPagina, [5, 10, 20, 50], true) ? $porPagina : 5;
    $busqueda = trim((string)($_GET['busqueda'] ?? ''));
    $columna = (string)($_GET['orden'] ?? 'id');
    $direccion = strtoupper((string)($_GET['direccion'] ?? 'ASC'));

    $columnasPermitidas = ['id', 'nombre', 'puesto', 'departamento', 'salario', 'fecha_ingreso'];
    if (!in_array($columna, $columnasPermitidas, true)) {
        $columna = 'id';
    }
    if (!in_array($direccion, ['ASC', 'DESC'], true)) {
        $direccion = 'ASC';
    }

    $where = '';
    $parametros = [];
    if ($busqueda !== '') {
        $where = " WHERE nombre LIKE :busqueda OR puesto LIKE :busqueda OR departamento LIKE :busqueda ";
        $parametros['busqueda'] = '%' . $busqueda . '%';
    }

    $conteo = $conexion->prepare("SELECT COUNT(*) FROM empleados {$where}");
    $conteo->execute($parametros);
    $total = (int)$conteo->fetchColumn();
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT id, nombre, puesto, departamento, salario, fecha_ingreso
            FROM empleados {$where}
            ORDER BY {$columna} {$direccion}
            LIMIT :limite OFFSET :offset";
    $stmt = $conexion->prepare($sql);
    foreach ($parametros as $clave => $valor) {
        $stmt->bindValue(':' . $clave, $valor, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    responderJson([
        'ok' => true,
        'empleados' => $stmt->fetchAll(),
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $porPagina,
        'total_paginas' => $totalPaginas,
    ]);
}

if ($metodo !== 'POST') {
    responderJson(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

exigirToken();
$accion = (string)($_POST['accion'] ?? '');

function validarEmpleado(): array
{
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $puesto = trim((string)($_POST['puesto'] ?? ''));
    $departamento = trim((string)($_POST['departamento'] ?? ''));
    $salarioTexto = (string)($_POST['salario'] ?? '');
    $fecha = (string)($_POST['fecha_ingreso'] ?? '');

    if ($nombre === '' || $puesto === '' || $departamento === '' || $salarioTexto === '' || $fecha === '') {
        responderJson(['ok' => false, 'mensaje' => 'Todos los campos son obligatorios.'], 422);
    }
    if (!is_numeric($salarioTexto) || (float)$salarioTexto < 0) {
        responderJson(['ok' => false, 'mensaje' => 'El salario debe ser un número igual o mayor que cero.'], 422);
    }
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        responderJson(['ok' => false, 'mensaje' => 'La fecha de ingreso no es válida.'], 422);
    }

    return [
        'nombre' => $nombre,
        'puesto' => $puesto,
        'departamento' => $departamento,
        'salario' => number_format((float)$salarioTexto, 2, '.', ''),
        'fecha_ingreso' => $fecha,
    ];
}

try {
    if ($accion === 'crear') {
        $datos = validarEmpleado();
        $stmt = $conexion->prepare(
            'INSERT INTO empleados (nombre, puesto, departamento, salario, fecha_ingreso)
             VALUES (:nombre, :puesto, :departamento, :salario, :fecha_ingreso)'
        );
        $stmt->execute($datos);
        responderJson(['ok' => true, 'mensaje' => 'Empleado registrado correctamente.']);
    }

    if ($accion === 'actualizar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            responderJson(['ok' => false, 'mensaje' => 'Identificador no válido.'], 422);
        }
        $datos = validarEmpleado();
        $datos['id'] = $id;
        $stmt = $conexion->prepare(
            'UPDATE empleados SET nombre=:nombre, puesto=:puesto, departamento=:departamento,
             salario=:salario, fecha_ingreso=:fecha_ingreso WHERE id=:id'
        );
        $stmt->execute($datos);
        responderJson(['ok' => true, 'mensaje' => 'Empleado actualizado correctamente.']);
    }

    if ($accion === 'eliminar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            responderJson(['ok' => false, 'mensaje' => 'Identificador no válido.'], 422);
        }
        $stmt = $conexion->prepare('DELETE FROM empleados WHERE id = :id');
        $stmt->execute(['id' => $id]);
        responderJson(['ok' => true, 'mensaje' => 'Empleado eliminado correctamente.']);
    }

    responderJson(['ok' => false, 'mensaje' => 'Acción no reconocida.'], 400);
} catch (PDOException $e) {
    responderJson(['ok' => false, 'mensaje' => 'No fue posible completar la operación.'], 500);
}

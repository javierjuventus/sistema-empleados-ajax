<?php
require_once __DIR__ . '/php/autenticacion.php';
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}
$csrf = crearToken();
$usuario = htmlspecialchars((string)($_SESSION['usuario'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Empleados | Sistema de Gestión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="index.php">Sistema de Empleados</a>
      <div class="d-flex align-items-center gap-3 text-white">
        <span class="small">Usuario: <?= $usuario ?></span>
        <button id="btnSalir" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Gestión de empleados</h1>
        <p class="text-secondary mb-0">Consulta y administra los registros mediante AJAX.</p>
      </div>
      <button class="btn btn-primary" id="btnNuevo">+ Agregar empleado</button>
    </div>

    <div id="mensaje" class="alert d-none" role="alert"></div>

    <section class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="row g-3 align-items-end mb-3">
          <div class="col-md-6">
            <label for="busqueda" class="form-label">Buscar empleado</label>
            <input type="search" id="busqueda" class="form-control" placeholder="Nombre, puesto o departamento...">
          </div>
          <div class="col-md-3">
            <label for="porPagina" class="form-label">Registros por página</label>
            <select id="porPagina" class="form-select">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
          </div>
          <div class="col-md-3">
            <div class="small text-secondary" id="totalRegistros">Cargando registros...</div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="id">ID</button></th>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="nombre">Nombre</button></th>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="puesto">Puesto</button></th>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="departamento">Departamento</button></th>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="salario">Salario</button></th>
                <th><button class="btn btn-link p-0 text-decoration-none ordenar" data-columna="fecha_ingreso">Fecha de ingreso</button></th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaEmpleados">
              <tr><td colspan="7" class="text-center text-secondary py-4">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <span class="small text-secondary" id="infoPagina"></span>
          <nav aria-label="Paginación">
            <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
          </nav>
        </div>
      </div>
    </section>
    <p class="text-secondary small text-center mt-4">Aplicación académica desarrollada con jQuery, AJAX, PHP, PDO y MySQL.</p>
  </main>

  <div class="modal fade" id="modalEmpleado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formEmpleado">
          <div class="modal-header">
            <h2 class="modal-title fs-5" id="tituloModal">Agregar empleado</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="empleadoId" name="id">
            <div class="mb-3"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="100" required></div>
            <div class="mb-3"><label class="form-label" for="puesto">Puesto</label><input class="form-control" id="puesto" name="puesto" maxlength="100" required></div>
            <div class="mb-3"><label class="form-label" for="departamento">Departamento</label><input class="form-control" id="departamento" name="departamento" maxlength="100" required></div>
            <div class="mb-3"><label class="form-label" for="salario">Salario</label><input class="form-control" id="salario" name="salario" type="number" min="0" step="0.01" required></div>
            <div class="mb-3"><label class="form-label" for="fecha_ingreso">Fecha de ingreso</label><input class="form-control" id="fecha_ingreso" name="fecha_ingreso" type="date" required></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>window.APP_CSRF_TOKEN = <?= json_encode($csrf) ?>;</script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/app.js"></script>
</body>
</html>

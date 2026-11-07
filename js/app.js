$(function () {
  let pagina = 1;
  let orden = 'id';
  let direccion = 'ASC';
  let empleadoActual = null;
  const modal = new bootstrap.Modal(document.getElementById('modalEmpleado'));

  cargarEmpleados();

  $('#busqueda').on('input', function () {
    pagina = 1;
    cargarEmpleados();
  });

  $('#porPagina').on('change', function () {
    pagina = 1;
    cargarEmpleados();
  });

  $('.ordenar').on('click', function () {
    const nuevaColumna = $(this).data('columna');
    direccion = (orden === nuevaColumna && direccion === 'ASC') ? 'DESC' : 'ASC';
    orden = nuevaColumna;
    cargarEmpleados();
  });

  $('#paginacion').on('click', 'button', function () {
    pagina = Number($(this).data('pagina'));
    cargarEmpleados();
  });

  $('#btnNuevo').on('click', function () {
    empleadoActual = null;
    $('#formEmpleado')[0].reset();
    $('#empleadoId').val('');
    $('#tituloModal').text('Agregar empleado');
    modal.show();
  });

  $('#tablaEmpleados').on('click', '.btn-editar', function () {
    empleadoActual = $(this).data('empleado');
    $('#formEmpleado')[0].reset();
    $('#empleadoId').val(empleadoActual.id);
    $('#nombre').val(empleadoActual.nombre);
    $('#puesto').val(empleadoActual.puesto);
    $('#departamento').val(empleadoActual.departamento);
    $('#salario').val(empleadoActual.salario);
    $('#fecha_ingreso').val(empleadoActual.fecha_ingreso);
    $('#tituloModal').text('Editar empleado');
    modal.show();
  });

  $('#tablaEmpleados').on('click', '.btn-eliminar', function () {
    const id = $(this).data('id');
    if (!window.confirm('¿Deseas eliminar este empleado?')) return;
    enviarAccion({ accion: 'eliminar', id: id }, function (respuesta) {
      mostrar(respuesta.mensaje, 'success');
      cargarEmpleados();
    });
  });

  $('#formEmpleado').on('submit', function (evento) {
    evento.preventDefault();
    const datos = $(this).serializeArray();
    datos.push({ name: 'accion', value: empleadoActual ? 'actualizar' : 'crear' });
    enviarAccion($.param(datos), function (respuesta) {
      modal.hide();
      mostrar(respuesta.mensaje, 'success');
      cargarEmpleados();
    });
  });

  $('#btnSalir').on('click', function () {
    if (!window.confirm('¿Deseas cerrar la sesión?')) return;
    enviarAccion({ url: 'php/logout.php', data: {}, success: function () {
      window.location.href = 'login.html';
    }});
  });

  function cargarEmpleados() {
    $.ajax({
      url: 'php/empleados.php',
      method: 'GET',
      dataType: 'json',
      data: {
        pagina: pagina,
        por_pagina: $('#porPagina').val(),
        busqueda: $('#busqueda').val(),
        orden: orden,
        direccion: direccion
      }
    }).done(function (respuesta) {
      if (!respuesta.ok) {
        mostrar(respuesta.mensaje, 'danger');
        return;
      }
      pagina = respuesta.pagina;
      $('#totalRegistros').text('Total de registros: ' + respuesta.total);
      $('#infoPagina').text('Página ' + respuesta.pagina + ' de ' + respuesta.total_paginas);
      dibujarTabla(respuesta.empleados);
      dibujarPaginacion(respuesta.pagina, respuesta.total_paginas);
    }).fail(function (xhr) {
      if (xhr.status === 401) {
        window.location.href = 'login.html';
        return;
      }
      mostrar(xhr.responseJSON?.mensaje || 'Error al consultar los empleados.', 'danger');
    });
  }

  function dibujarTabla(empleados) {
    const $tabla = $('#tablaEmpleados').empty();
    if (!empleados.length) {
      $tabla.append('<tr><td colspan="7" class="text-center text-secondary py-4">No se encontraron empleados.</td></tr>');
      return;
    }
    empleados.forEach(function (empleado) {
      const $fila = $('<tr>');
      [
        empleado.id, empleado.nombre, empleado.puesto, empleado.departamento,
        '$' + Number(empleado.salario).toLocaleString('es-MX', { minimumFractionDigits: 2 }),
        empleado.fecha_ingreso
      ].forEach(function (valor) {
        $('<td>').text(valor).appendTo($fila);
      });
      const $acciones = $('<td>');
      $('<button type="button" class="btn btn-sm btn-outline-primary me-1">Editar</button>')
        .addClass('btn-editar').data('empleado', empleado).appendTo($acciones);
      $('<button type="button" class="btn btn-sm btn-outline-danger">Eliminar</button>')
        .addClass('btn-eliminar').data('id', empleado.id).appendTo($acciones);
      $fila.append($acciones);
      $tabla.append($fila);
    });
  }

  function dibujarPaginacion(actual, total) {
    const $lista = $('#paginacion').empty();
    agregarBoton('Anterior', actual - 1, actual <= 1);
    for (let i = 1; i <= total; i++) {
      agregarBoton(String(i), i, false, i === actual);
    }
    agregarBoton('Siguiente', actual + 1, actual >= total);
    function agregarBoton(texto, numero, deshabilitado, activo) {
      const $li = $('<li>').addClass('page-item');
      if (deshabilitado) $li.addClass('disabled');
      if (activo) $li.addClass('active');
      const $btn = $('<button type="button" class="page-link">').text(texto).data('pagina', numero);
      if (deshabilitado || activo) $btn.prop('disabled', true);
      $li.append($btn);
      $lista.append($li);
    }
  }

  function enviarAccion(datos, alExito) {
    let opciones;
    if (datos && datos.url) {
      opciones = {
        url: datos.url,
        method: 'POST',
        data: datos.data,
        dataType: 'json',
        headers: { 'X-CSRF-Token': window.APP_CSRF_TOKEN }
      };
      alExito = datos.success;
    } else {
      opciones = {
        url: 'php/empleados.php',
        method: 'POST',
        data: datos,
        dataType: 'json',
        headers: { 'X-CSRF-Token': window.APP_CSRF_TOKEN }
      };
    }
    $.ajax(opciones).done(function (respuesta) {
      if (respuesta.ok) {
        if (alExito) alExito(respuesta);
      } else {
        mostrar(respuesta.mensaje, 'danger');
      }
    }).fail(function (xhr) {
      if (xhr.status === 401) {
        window.location.href = 'login.html';
        return;
      }
      mostrar(xhr.responseJSON?.mensaje || 'No fue posible completar la operación.', 'danger');
    });
  }

  function mostrar(texto, tipo) {
    $('#mensaje').removeClass('d-none alert-success alert-danger')
      .addClass('alert-' + tipo).text(texto);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

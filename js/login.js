$(function () {
  const $form = $('#formLogin');
  const $mensaje = $('#mensaje');

  // Solicita un token de formulario para proteger el envío del inicio de sesión.
  $.getJSON('php/token_login.php')
    .done(function (respuesta) {
      $('#csrf_token').val(respuesta.csrf_token);
    })
    .fail(function () {
      mostrar('No fue posible preparar el formulario. Recarga la página.', 'danger');
    });

  $form.on('submit', function (evento) {
    evento.preventDefault();
    $('#btnLogin').prop('disabled', true).text('Validando...');
    $.ajax({
      url: 'php/login.php',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json'
    }).done(function (respuesta) {
      if (respuesta.ok) {
        window.location.href = 'index.php';
      } else {
        mostrar(respuesta.mensaje, 'danger');
      }
    }).fail(function (xhr) {
      mostrar(xhr.responseJSON?.mensaje || 'No fue posible iniciar sesión.', 'danger');
    }).always(function () {
      $('#btnLogin').prop('disabled', false).text('Ingresar');
    });
  });

  function mostrar(texto, tipo) {
    $mensaje.removeClass('d-none alert-success alert-danger')
      .addClass('alert-' + tipo).text(texto);
  }
});

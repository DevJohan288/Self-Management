document
  .getElementById("logout-btn")
  .addEventListener("click", function (event) {
    event.preventDefault();
    Swal.fire({
      title: "¿Estás seguro?",
      text: "¿Quieres cerrar sesión?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, cerrar sesión",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        // Enviar POST a logout.php
        fetch('/Self-Management/app/logout.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: ''
        }).then(response => {
          // Redirigir al index aunque la respuesta sea 200
          window.location.href = '/Self-Management/index.php';
        }).catch(err => {
          // En caso de error, igual redirigir
          window.location.href = '/Self-Management/index.php';
        });
      }
    });
  });

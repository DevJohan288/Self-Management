let bootstrapLoaded = false;

function cargarBootstrap(callback) {
  if (bootstrapLoaded) {
    callback();
    return;
  }

  // Cargar CSS
  const link = document.createElement("link");
  link.rel = "stylesheet";
  link.href =
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";
  document.head.appendChild(link);

  // Cargar JS
  const script = document.createElement("script");
  script.src =
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js";
  script.onload = () => {
    bootstrapLoaded = true;
    callback();
  };
  document.body.appendChild(script);
}

document.getElementById("abrirModal").addEventListener("click", () => {
  cargarBootstrap(() => {
    const modal = new bootstrap.Modal(document.getElementById("miModal"));
    modal.show();
  });
});

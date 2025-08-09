// Modal.js - Componente modal reutilizable estilo Bootstrap
// Uso: modal.open({ title, body, footer })

class Modal {
  constructor() {
    this.modal = null;
    this.overlay = null;
    this._init();
  }

  _init() {
    // Crear overlay
    this.overlay = document.createElement("div");
    this.overlay.className = "custom-modal-overlay";
    this.overlay.style.display = "none";
    document.body.appendChild(this.overlay);

    // Crear modal
    this.modal = document.createElement("div");
    this.modal.className = "custom-modal";
    this.overlay.appendChild(this.modal);

    // Cerrar al hacer click fuera
    this.overlay.addEventListener("mousedown", (e) => {
      if (e.target === this.overlay) this.close();
    });

    // Cerrar con Escape
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.overlay.style.display === "block") {
        this.close();
      }
    });
  }

  open({ title = "", body = "", footer = "" } = {}) {
    this.modal.innerHTML = `
      <div class="custom-modal-content">
        <div class="custom-modal-header">
          <h5>${title}</h5>
          <button class="custom-modal-close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="custom-modal-body">${body}</div>
        <div class="custom-modal-footer">${footer}</div>
      </div>
    `;
    this.overlay.style.display = "block";
    this.modal.querySelector(".custom-modal-close").onclick = () =>
      this.close();
  }

  close() {
    this.overlay.style.display = "none";
    this.modal.innerHTML = "";
  }
}

// Instancia global
window.modal = new Modal();

/**
 * ALISER - Main JavaScript (Versión Final Corregida)
 */
(function() {
  'use strict';

  const CONFIG = {
    BASE_URL: window.location.origin + '/aliser-web/'
  };

  document.addEventListener('DOMContentLoaded', () => {
    initApp();
  });

  function initApp() {
    console.log('ALISER Platform inicializada');
    initTerrenosModal();
    fetchData();
  }

  function initTerrenosModal() {
    const modal = document.getElementById('modal-terreno');
    const btnAbrir = document.querySelector('a[href="#terrenos-section"]');
    const btnCerrar = document.getElementById('close-terreno');
    const form = document.getElementById('form-oferta-terreno');

    if (btnAbrir && modal) {
      btnAbrir.onclick = (e) => {
        e.preventDefault();
        modal.style.display = 'flex';
        console.log('Modal abierto');
      };
    }

    if (btnCerrar) {
      btnCerrar.onclick = () => {
        modal.style.display = 'none';
      };
    }

    // Plus: Cerrar si hace clic en el fondo oscuro
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    if (form) {
      form.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        try {
          const response = await fetch(`${CONFIG.BASE_URL}frontend/api/save_terreno.php`, {
            method: 'POST',
            body: formData
          });
          const result = await response.json();
          alert(result.message);
          if (result.success) {
            form.reset();
            modal.style.display = 'none';
          }
        } catch (error) {
          console.error('Error:', error);
          alert('Error de conexión con el servidor.');
        }
      };
    }
  }

  async function fetchData() {
    try {
      const response = await fetch(`${CONFIG.BASE_URL}api/get_data.php`);
      const data = await response.json();
      console.log('Datos cargados:', data);
    } catch (error) {
      console.error('Error en fetchData:', error);
    }
  }
})();
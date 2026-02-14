/**
 * ALISER - Modulo Frontend Promociones
 */
(function () {
  'use strict';

  const CONFIG = {
    BASE_URL: window.location.origin + '/aliser-web/',
    WHATSAPP_MAYOREO: '521234567890'
  };

  const state = {
    promociones: [],
    tipoActivo: 'menudeo'
  };

  document.addEventListener('DOMContentLoaded', initPromociones);

  function initPromociones() {
    const grid = document.getElementById('promociones-grid');
    const status = document.getElementById('promociones-status');
    const tabMenudeo = document.getElementById('promo-tab-menudeo');
    const tabMayoreo = document.getElementById('promo-tab-mayoreo');

    if (!grid || !status || !tabMenudeo || !tabMayoreo) return;

    tabMenudeo.addEventListener('click', () => switchTab('menudeo'));
    tabMayoreo.addEventListener('click', () => switchTab('mayoreo'));

    fetchPromociones();
  }

  async function fetchPromociones() {
    const status = document.getElementById('promociones-status');
    status.textContent = 'Cargando promociones...';

    try {
      const response = await fetch(`${CONFIG.BASE_URL}api/get_promociones.php`, { method: 'GET' });
      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || 'Error al cargar promociones');
      }

      state.promociones = Array.isArray(data.promociones) ? data.promociones : [];
      renderPromociones();
    } catch (error) {
      console.error('Promociones fetch error:', error);
      status.textContent = 'No se pudieron cargar promociones en este momento.';
    }
  }

  function switchTab(tipo) {
    state.tipoActivo = tipo;
    const tabMenudeo = document.getElementById('promo-tab-menudeo');
    const tabMayoreo = document.getElementById('promo-tab-mayoreo');

    if (tipo === 'menudeo') {
      tabMenudeo.classList.add('active');
      tabMenudeo.setAttribute('aria-selected', 'true');
      tabMayoreo.classList.remove('active');
      tabMayoreo.setAttribute('aria-selected', 'false');
    } else {
      tabMayoreo.classList.add('active');
      tabMayoreo.setAttribute('aria-selected', 'true');
      tabMenudeo.classList.remove('active');
      tabMenudeo.setAttribute('aria-selected', 'false');
    }

    renderPromociones();
  }

  function renderPromociones() {
    const grid = document.getElementById('promociones-grid');
    const status = document.getElementById('promociones-status');
    if (!grid || !status) return;

    const filtradas = state.promociones.filter((promo) => promo.tipo_publico === state.tipoActivo);
    grid.innerHTML = '';

    if (filtradas.length === 0) {
      status.textContent = 'No hay promociones activas para esta vista.';
      return;
    }

    status.textContent = '';

    filtradas.forEach((promo) => {
      const flyerName = promo.imagen_flyer ? String(promo.imagen_flyer).trim() : '';
      const flyerUrl = flyerName
        ? `${CONFIG.BASE_URL}assets/img/promociones/${encodeURIComponent(flyerName)}`
        : `${CONFIG.BASE_URL}assets/img/vacantes/no-image.webp`;

      const titulo = escapeHtml(promo.titulo || 'Promoción ALISER');
      const descripcion = truncateText(escapeHtml(promo.descripcion || ''), 150);
      const fechaFin = formatDate(promo.fecha_fin);
      const esMenudeo = promo.tipo_publico === 'menudeo';

      const card = document.createElement('article');
      card.className = 'promo-card-glass';
      card.innerHTML = `
        <div class="promo-image-wrap">
          <img src="${flyerUrl}" alt="${titulo}" class="promo-image" loading="lazy" onerror="this.src='${CONFIG.BASE_URL}assets/img/vacantes/no-image.webp'">
        </div>
        <div class="promo-content">
          <h3 class="promo-title-shimmer">${titulo}</h3>
          <p class="promo-description">${descripcion}</p>
          <p class="promo-vigencia">Válido hasta: <strong>${fechaFin}</strong></p>
          <div class="promo-actions">
            ${esMenudeo ? renderBtnMenudeo(flyerUrl) : renderBtnMayoreo(promo.titulo || 'Promoción')}
          </div>
        </div>
      `;

      grid.appendChild(card);
    });
  }

  function renderBtnMenudeo(flyerUrl) {
    return `<a class="promo-btn-primary" href="${flyerUrl}" target="_blank" rel="noopener noreferrer">Descargar Cupón</a>`;
  }

  function renderBtnMayoreo(titulo) {
    const mensaje = `Hola, vi la promoción de ${titulo} para Mayoreo y me gustaría cotizar para mi negocio.`;
    const waUrl = `https://wa.me/${CONFIG.WHATSAPP_MAYOREO}?text=${encodeURIComponent(mensaje)}`;
    return `<a class="promo-btn-primary" href="${waUrl}" target="_blank" rel="noopener noreferrer">Contactar Asesor</a>`;
  }

  function formatDate(dateString) {
    if (!dateString) return 'Sin fecha';
    const parts = String(dateString).split('-');
    if (parts.length !== 3) return dateString;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }

  function truncateText(text, maxLength) {
    return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text;
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();

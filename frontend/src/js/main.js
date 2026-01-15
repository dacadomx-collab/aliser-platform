/**
 * ALISER - Main JavaScript
 * Comportamiento principal de la aplicación
 */

(function() {
  'use strict';

  // Inicialización cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function() {
    initApp();
  });

  /**
   * Inicializa la aplicación
   */
  function initApp() {
    console.log('ALISER Platform inicializada');
    
    // Aquí se inicializarán los módulos principales
    initNavigation();
    initAccessibility();
  }

  /**
   * Inicializa la navegación
   */
  function initNavigation() {
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');

    if (!menuToggle) {
      return;
    }

    // Cerrar menú móvil al hacer clic en un enlace
    if (mobileMenuLinks.length > 0) {
      mobileMenuLinks.forEach(function(link) {
        link.addEventListener('click', function() {
          menuToggle.checked = false;
        });
      });
    }

    // Resetear burger menu cuando se cambia a desktop (resize)
    function handleResize() {
      if (window.innerWidth >= 992 && menuToggle.checked) {
        menuToggle.checked = false;
      }
    }

    // Throttle para mejorar performance
    let resizeTimeout;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(handleResize, 150);
    });

    // Verificar al cargar si estamos en desktop
    handleResize();
  }

  /**
   * Inicializa mejoras de accesibilidad
   */
  function initAccessibility() {
    // TODO: Implementar mejoras de accesibilidad
  }

  /**
   * Inicializa animaciones por scroll
   */
  function initScrollAnimations() {
    const footer = document.querySelector('.main-footer');
    const terrenosSection = document.querySelector('.terrenos-section');

    // Función para activar animaciones
    function activateAnimations(element) {
      if (element) {
        element.style.opacity = '1';
      }
    }

    // Usar Intersection Observer para activar animaciones al scroll
    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          activateAnimations(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });

    // Observar elementos
    if (footer) observer.observe(footer);
    if (terrenosSection) observer.observe(terrenosSection);
  }

  // Inicializar scroll animations
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollAnimations);
  } else {
    initScrollAnimations();
  }

  // Exponer funciones globales si es necesario
  window.ALISER = {
    init: initApp
  };

})();

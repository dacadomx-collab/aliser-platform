# 🚀 ALISER 2026 - Guía de Desarrollo en Cursor

Este documento es la referencia para mantener la calidad, sofisticación y SEO del proyecto.

## 1. Estructura de Archivos (Separación de Preocupaciones)
- `/assets/css/`: Estilos (Usar metodología BEM).
- `/assets/js/`: Lógica e integración con IA.
- `/assets/vendors/`: Librerías externas (Bootstrap, GSAP, etc.).
- `/img/`: Optimizar siempre a formato WebP o AVIF.

## 2. Estándares de "Gran Impacto Visual"
- **Tipografía:** Usar fuentes limpias y modernas (Sans-serif).
- **Animaciones:** Implementar micro-interacciones suaves en botones y carga de datos.
- **Mobile-First:** Todo debe ser funcional y elegante en dispositivos móviles antes que en desktop.

## 3. SEO & AIO (AI Optimization)
- Mantener el bloque `JSON-LD` en el `<head>` actualizado.
- Usar etiquetas semánticas de HTML5 (`<article>`, `<section>`, `<nav>`).
- Alt text descriptivo en todas las imágenes para Google Lens y buscadores visuales.

## 4. Flujo de Trabajo Git
1. `git pull origin main` (Antes de empezar).
2. Realizar cambios en Cursor.
3. `git add .`
4. `git commit -m "tipo: descripción corta"`
   - Tipos: `feat:` (nueva función), `fix:` (error), `style:` (diseño), `chore:` (configuración).
5. `git push origin main`.

---
*Mantenemos la excelencia de ALISER en cada línea de código.*
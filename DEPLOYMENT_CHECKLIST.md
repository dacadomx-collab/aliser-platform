# 🚀 ALISER - Checklist de Despliegue (Producción)
Este documento detalla los cambios críticos para pasar de LOCAL (XAMPP) a PRODUCCIÓN (Hosting).

## 1. Base de Datos (Admin/Includes/db.php)
- [ ] Cambiar 'localhost' por la IP o Host del servidor de producción.
- [ ] Cambiar 'aliser_db' por el nombre de la DB en el hosting.
- [ ] Cambiar 'root' y '' por el usuario y contraseña del hosting.

## 2. Frontend (Configuración JS)
- [ ] En `frontend/src/js/main.js`, verificar que `CONFIG.BASE_URL` apunte al dominio: `https://aliser.mx/`.

## 3. Servidor Apache (.htaccess)
- [ ] Verificar `RewriteBase /` en el archivo `.htaccess`.
- [ ] Asegurar que el módulo `mod_rewrite` esté activo en el hosting.
- [ ] Forzar redirección HTTPS.

## 4. Permisos de Archivos (CHMOD)
- [ ] Carpeta `assets/img/vacantes/` -> Permisos 755 o 777.
- [ ] Carpeta `assets/img/terrenos/` -> Permisos 755 o 777.
[x] Verificado: Estilos consolidados en archivo externo.
[x] Verificado: Rutas de imagen apuntan a assets/img/terrenos/.

## 5. Seguridad Final
- [ ] Borrar cualquier archivo de prueba (test_password.php, fix_access.php).
- [ ] Cambiar la contraseña del usuario 'admin' por una de alta seguridad.

## 🛠️ Correcciones Críticas de Estructura (Febrero 2026)

### 1. Estandarización de Imagenes (image_helper.php)
- [ ] **Acción:** Reemplazar contenido total de `admin/includes/image_helper.php`.
- [ ] **Cambio en Línea 72:** Se ajustó para que la base de datos guarde solo el nombre del archivo (ej. `vacante_123.webp`) y no la ruta completa, evitando errores 404 por rutas duplicadas.
- [ ] **Cambio en Línea 112:** Se corrigió el apuntador de ruta física para compatibilidad con la raíz de XAMPP.

### 2. Reparación de Estilos y Funciones CRUD
- [ ] **Acción:** Corregir `nueva_vacante.php` usando rutas raíz `/aliser-web/admin/css/` para evitar pérdida de estilos.
- [ ] **Acción:** Corregir `vacantes.php` moviendo la lógica de eliminación DESPUÉS de la inicialización de `$db` para evitar el Fatal Error en la línea 11.
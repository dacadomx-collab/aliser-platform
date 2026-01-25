# ALISER - Scripts de Base de Datos

## Instrucciones de Instalación

### 1. Importar la Base de Datos

**Opción A: Desde phpMyAdmin**
1. Abre phpMyAdmin en `http://localhost/phpmyadmin`
2. Haz clic en "Importar" (Import)
3. Selecciona el archivo `database.sql`
4. Haz clic en "Continuar" (Go)

**Opción B: Desde Línea de Comandos**
```bash
# Desde la carpeta sql/
mysql -u root -p < database.sql
```

### 2. Generar Hash de Contraseña (Opcional)

Si necesitas generar un nuevo hash para una contraseña:

**Opción A: Usando el script PHP**
```bash
php generate_password_hash.php
```

**Opción B: Desde PHP interactivo**
```php
<?php
echo password_hash('tu_contraseña', PASSWORD_BCRYPT);
?>
```

### 3. Usuario de Prueba

El script incluye un usuario de prueba:
- **Usuario:** `admin`
- **Contraseña:** `Admin123!`
- **Rol:** `admin`
- **Email:** `admin@aliser.mx`

⚠️ **IMPORTANTE:** Cambiar esta contraseña después del primer login.

### 4. Configurar Conexión PHP

Edita el archivo `admin/includes/db.php` y actualiza las constantes si es necesario:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aliser_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Tu contraseña de MySQL
```

## Estructura de la Base de Datos

### Tabla: `usuarios_admin`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT(11) UNSIGNED | ID único (Auto Increment) |
| nombre_completo | VARCHAR(100) | Nombre completo del usuario |
| usuario | VARCHAR(50) | Nombre de usuario único |
| password | VARCHAR(255) | Hash de la contraseña (bcrypt) |
| email | VARCHAR(100) | Correo electrónico único |
| rol | ENUM('admin', 'editor') | Rol del usuario |
| activo | TINYINT(1) | Estado (1=activo, 0=inactivo) |
| ultimo_acceso | DATETIME | Fecha del último acceso |
| creado_en | TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | Fecha de última actualización |

## Seguridad

- Las contraseñas se almacenan usando `password_hash()` con bcrypt
- Usa prepared statements en todas las consultas
- Valida y sanitiza todos los inputs del usuario
- Implementa rate limiting para el login
- Usa HTTPS en producción

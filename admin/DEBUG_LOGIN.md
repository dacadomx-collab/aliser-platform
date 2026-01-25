# ALISER - Guía de Debug para Login

## Problemas Comunes y Soluciones

### 1. Error: "Usuario o contraseña incorrectos"

#### Verificar el hash en la base de datos:

1. **Generar un hash válido:**
   ```bash
   php sql/update_password_hash.php
   ```
   O desde el navegador: `http://localhost/aliser-web/sql/update_password_hash.php`

2. **Actualizar el hash en la base de datos:**
   - Abre phpMyAdmin
   - Selecciona la base de datos `aliser_db`
   - Ve a la tabla `usuarios_admin`
   - Edita el usuario `admin`
   - Reemplaza el campo `password` con el nuevo hash generado
   - Guarda los cambios

3. **O actualizar directamente con SQL:**
   ```sql
   UPDATE usuarios_admin 
   SET password = 'NUEVO_HASH_AQUI' 
   WHERE usuario = 'admin';
   ```

### 2. Verificar que la base de datos esté conectada

1. **Verificar conexión en `admin/includes/db.php`:**
   - Host: `localhost`
   - Base de datos: `aliser_db`
   - Usuario: `root`
   - Contraseña: (vacía por defecto en XAMPP)

2. **Probar conexión:**
   - Crea un archivo `admin/test_connection.php`:
   ```php
   <?php
   require_once 'includes/db.php';
   try {
       $db = getDB();
       echo "✓ Conexión exitosa";
   } catch (Exception $e) {
       echo "✗ Error: " . $e->getMessage();
   }
   ?>
   ```

### 3. Verificar logs de error

Los errores se registran en:
- **XAMPP Windows:** `C:\xampp\php\logs\php_error_log`
- **O en:** `C:\xampp\apache\logs\error.log`

Revisa estos archivos para ver mensajes de debug.

### 4. Verificar que el usuario existe

Ejecuta en phpMyAdmin:
```sql
SELECT id, usuario, email, rol, activo, 
       LENGTH(password) as hash_length,
       LEFT(password, 20) as hash_preview
FROM usuarios_admin 
WHERE usuario = 'admin';
```

Debe mostrar:
- `usuario`: admin
- `activo`: 1
- `hash_length`: 60 (longitud de hash bcrypt)
- `hash_preview`: $2y$10$... (debe empezar con $2y$)

### 5. Probar autenticación directamente

Crea `admin/test_auth.php`:
```php
<?php
require_once 'includes/db.php';

$password = 'Admin123!';
$db = getDB();
$user = $db->fetchOne(
    "SELECT * FROM usuarios_admin WHERE usuario = :usuario",
    ['usuario' => 'admin']
);

if ($user) {
    echo "Usuario encontrado: " . $user['usuario'] . "\n";
    echo "Hash: " . substr($user['password'], 0, 30) . "...\n";
    
    if (password_verify($password, $user['password'])) {
        echo "✓ Contraseña VÁLIDA\n";
    } else {
        echo "✗ Contraseña INVÁLIDA\n";
        echo "Genera nuevo hash con: php sql/update_password_hash.php\n";
    }
} else {
    echo "✗ Usuario no encontrado\n";
}
?>
```

## Solución Rápida

Si nada funciona, ejecuta este SQL en phpMyAdmin para crear un usuario con hash válido:

```sql
-- Primero, genera el hash ejecutando: php sql/update_password_hash.php
-- Luego reemplaza NUEVO_HASH_AQUI con el hash generado

UPDATE usuarios_admin 
SET password = 'NUEVO_HASH_AQUI'
WHERE usuario = 'admin';

-- O inserta un nuevo usuario de prueba:
INSERT INTO usuarios_admin 
  (nombre_completo, usuario, password, email, rol, activo) 
VALUES 
  (
    'Admin Test',
    'test',
    'NUEVO_HASH_AQUI',
    'test@aliser.mx',
    'admin',
    1
  );
```

## Notas Importantes

- El hash bcrypt siempre tiene 60 caracteres
- Debe empezar con `$2y$10$` o `$2y$12$`
- Cada vez que generas un hash, es diferente (por el salt)
- Usa `password_verify()` para verificar, nunca compares directamente

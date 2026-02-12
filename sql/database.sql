-- ============================================
-- ALISER - Script de Base de Datos
-- Creación de base de datos y tablas
-- ============================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `aliser_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE `aliser_db`;

-- ============================================
-- Tabla: usuarios_admin
-- Administradores y editores del sistema
-- ============================================

CREATE TABLE IF NOT EXISTS `usuarios_admin` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(100) NOT NULL COMMENT 'Nombre completo del usuario',
  `usuario` VARCHAR(50) NOT NULL COMMENT 'Nombre de usuario único para login',
  `password` VARCHAR(255) NOT NULL COMMENT 'Hash de la contraseña (bcrypt)',
  `email` VARCHAR(100) NOT NULL COMMENT 'Correo electrónico del usuario',
  `rol` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor' COMMENT 'Rol del usuario: admin o editor',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Estado del usuario (1=activo, 0=inactivo)',
  `ultimo_acceso` DATETIME NULL DEFAULT NULL COMMENT 'Fecha y hora del último acceso',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario` (`usuario`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_rol` (`rol`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de usuarios administradores del sistema ALISER';

-- ============================================
-- Usuario de Prueba Inicial
-- ============================================
-- Contraseña temporal: Admin123! (CAMBIAR después del primer login)
-- 
-- NOTA: Para generar un nuevo hash de contraseña, ejecutar:
-- php sql/generate_password_hash.php
-- O usar: SELECT PASSWORD('tu_contraseña') en MySQL (método antiguo)
-- 
-- Hash generado con password_hash() de PHP usando PASSWORD_BCRYPT
-- Este hash corresponde a la contraseña: Admin123!

INSERT INTO `usuarios_admin` 
  (`nombre_completo`, `usuario`, `password`, `email`, `rol`, `activo`) 
VALUES 
  (
    'Administrador Principal',
    'admin',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- Hash de: Admin123!
    'admin@aliser.mx',
    'admin',
    1
  );

-- ============================================
-- Verificación de datos insertados
-- ============================================

-- Mostrar los usuarios creados
SELECT 
  id,
  nombre_completo,
  usuario,
  email,
  rol,
  activo,
  creado_en
FROM `usuarios_admin`;

-- ============================================
-- Tabla: vacantes
-- Vacantes de trabajo de ALISER
-- ============================================

CREATE TABLE IF NOT EXISTS `vacantes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(200) NOT NULL COMMENT 'Título de la vacante',
  `descripcion` TEXT NOT NULL COMMENT 'Descripción detallada de la vacante',
  `imagen_flyer` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta de la imagen del flyer',
  `fecha_inicio` DATE NOT NULL COMMENT 'Fecha de inicio de la publicación',
  `fecha_fin` DATE NOT NULL COMMENT 'Fecha de finalización de la publicación',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Estado de la vacante (1=activa, 0=inactiva)',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_fecha_inicio` (`fecha_inicio`),
  KEY `idx_fecha_fin` (`fecha_fin`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de vacantes de trabajo de ALISER';

-- ============================================
-- Tabla: terrenos
-- Propuestas de terrenos para expansión de ALISER
-- ============================================

CREATE TABLE IF NOT EXISTS `terrenos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ubicacion` VARCHAR(255) NOT NULL COMMENT 'Ubicación del terreno',
  `superficie` DECIMAL(10,2) NOT NULL COMMENT 'Superficie del terreno en m²',
  `precio_sugerido` DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Precio sugerido del terreno',
  `imagen_terreno` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta de la imagen del terreno',
  `descripcion` TEXT NOT NULL COMMENT 'Descripción detallada del terreno',
  `estatus` ENUM('disponible', 'en_evaluacion', 'adquirido', 'rechazado') NOT NULL DEFAULT 'disponible' COMMENT 'Estatus del terreno',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_ubicacion` (`ubicacion`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de terrenos para expansión de ALISER';
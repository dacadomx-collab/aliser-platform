-- ALISER - Migracion usuarios_admin para multi-rol
-- Ejecutar sobre aliser_db

ALTER TABLE usuarios_admin
    ADD COLUMN email VARCHAR(150) NOT NULL DEFAULT '' AFTER nombre,
    ADD COLUMN whatsapp VARCHAR(20) NOT NULL DEFAULT '' AFTER email;

ALTER TABLE usuarios_admin
    MODIFY COLUMN rol ENUM('MASTER','TALENTO','BIENES','PROMO','MARCA','SOPORTE') NOT NULL DEFAULT 'SOPORTE';

UPDATE usuarios_admin
SET rol = CASE
    WHEN LOWER(rol) = 'admin' THEN 'MASTER'
    WHEN LOWER(rol) = 'editor' THEN 'SOPORTE'
    ELSE UPPER(rol)
END;

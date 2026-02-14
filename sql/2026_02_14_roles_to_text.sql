-- ALISER - Roles multiples (rol CSV)
-- Cambia usuarios_admin.rol a TEXT para permitir valores como "TALENTO,MARCA"

UPDATE usuarios_admin
SET rol = 'SOPORTE'
WHERE rol IS NULL OR rol = '';

-- Normalizar valores historicos si existieran
UPDATE usuarios_admin
SET rol = CASE
    WHEN LOWER(rol) = 'admin' THEN 'MASTER'
    WHEN LOWER(rol) = 'editor' THEN 'SOPORTE'
    ELSE rol
END;

ALTER TABLE usuarios_admin
    MODIFY COLUMN rol TEXT NOT NULL;

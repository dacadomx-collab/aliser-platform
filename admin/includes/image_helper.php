<?php
if (!defined('ALISER_ADMIN')) { define('ALISER_ADMIN', true); }

class ImageHelper {
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const WEBP_QUALITY = 85;

public static function processVacanteImage(array $file, string $destination_dir = 'assets/img/vacantes/'): array|false {
        try {
            self::validateImage($file);
            
            // Localización exacta en XAMPP
            $root_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
            $target_dir = $root_path . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destination_dir);

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // Generar nombre limpio
            $new_filename = 'vacante_' . time() . '_' . bin2hex(random_bytes(4)) . '.webp';
            $final_path = $target_dir . $new_filename;

            $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
            if (!$image) throw new Exception('Imagen corrupta.');

            imagepalettetotruecolor($image);
            imagewebp($image, $final_path, self::WEBP_QUALITY);
            imagedestroy($image);

            return [
                'filename' => $new_filename,
                'path' => $new_filename 
            ];
        } catch (Exception $e) {
            error_log("Error ImageHelper: " . $e->getMessage());
            return false;
        }
    }

    public static function validateImage(array $file): bool {
        if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Error de subida.');
        if ($file['size'] > self::MAX_FILE_SIZE) throw new Exception('Archivo muy grande.');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED_MIME_TYPES)) throw new Exception('Formato no permitido.');
        return true;
    }
}
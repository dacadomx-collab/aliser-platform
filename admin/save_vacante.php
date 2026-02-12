<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $sucursal = $_POST['sucursal'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $requisitos = $_POST['requisitos'] ?? '';
    $estatus = $_POST['estatus'] ?? 'activa';
    $imagen = '';

    // Procesar Imagen
    if (isset($_FILES['imagen_vacante']) && $_FILES['imagen_vacante']['error'] == 0) {
        $imgName = time() . '_' . $_FILES['imagen_vacante']['name'];
        move_uploaded_file($_FILES['imagen_vacante']['tmp_name'], "../assets/img/vacantes/" . $imgName);
        $imagen = $imgName;
    }

    $sql = "INSERT INTO vacantes (titulo, sucursal, descripcion, requisitos, imagen_vacante, estatus) 
            VALUES (:t, :s, :d, :r, :i, :e)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':t' => $titulo,
        ':s' => $sucursal,
        ':d' => $descripcion,
        ':r' => $requisitos,
        ':i' => $imagen,
        ':e' => $estatus
    ]);

    header("Location: vacantes.php?success=1");
    exit;
}
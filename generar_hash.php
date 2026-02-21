<?php
// La contraseña para admin de las paginas protegidas
$password = '*41i53R.26'; 

// Generamos el hash seguro
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Tu Hash es: <br><strong>" . $hash . "</strong>";
?>
<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);

$h = 'localhost';

$d = 'tecnidepot_aliser';

$u = 'tecnidepot_aliserDB';

$p = '0l@{F0w?cRS$w&nN';

try {

$conn = new PDO("mysql:host=$h;dbname=$d", $u, $p);

echo "CONECTADO EXITOSAMENTE";

} catch (Exception $e) {

echo "ERROR: " . $e->getMessage();

}

?>
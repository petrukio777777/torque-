<?php
$host = "localhost";
$user = "root"; 
$pass = "root";    
$dbname = "torque_plus";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
// Opcional: definir charset para acentos
$conn->set_charset("utf8");
?>
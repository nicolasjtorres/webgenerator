<?php
$host = "localhost";
$user = "adm_webgenerator";
$pass = "webgenerator2024";
$dbname = "webgenerator";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}
?>

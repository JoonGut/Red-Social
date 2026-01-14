<?php
$host = '18.208.57.228';
$db   = 'bd_social';
$user = 'usuario';
$pass = 'usuario';
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);

if ($mysqli->connect_errno) {
    die('Error de conexión MySQL: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

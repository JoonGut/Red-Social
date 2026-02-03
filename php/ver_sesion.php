<?php
session_start();

echo "<h1>🔍 Radiografía de tu Sesión</h1>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<hr>";

if (isset($_SESSION['id_rol'])) {
    echo "✅ Existe 'id_rol' y vale: " . $_SESSION['id_rol'];
} else {
    echo "❌ NO existe la variable 'id_rol'. <br>";
    echo "Revisa si se llama 'rol', 'role', 'tipo_usuario' o 'admin'.";
}
?>
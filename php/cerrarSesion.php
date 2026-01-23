<?php
session_start();
session_unset(); // Limpia las variables
session_destroy(); // Destruye la sesión

// Redirige al login (ajusta la ruta si tu login.html está en otra carpeta)
header("Location: ../login.html");
exit;
?>
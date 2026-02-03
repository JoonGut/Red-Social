<?php
declare(strict_types=1);
session_start();

// Si tu servidor muestra errores en pantalla, esto evita que rompan el JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// 1. SEGURIDAD: Verifica que siga siendo admin
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 2) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => '⛔ Acceso denegado. No eres administrador.']);
    exit;
}

try {
    // 2. EJECUTAR EL COMANDO
    // '2>&1' redirige los errores al output estándar para que podamos leerlos
    // Cambia 'main' por 'master' si tu rama se llama así.
    $comando = 'git pull origin main 2>&1';
    
    // Ejecutamos
    $output = shell_exec($comando);

    // Verificamos si shell_exec está habilitado
    if ($output === null) {
        throw new Exception("La función shell_exec() está deshabilitada en este servidor o falló la ejecución.");
    }

    // 3. RESPONDER
    echo json_encode([
        'ok' => true,
        'msg' => 'Comando ejecutado correctamente.',
        'output' => $output // Aquí va el texto que devuelve Git
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Error del servidor: ' . $e->getMessage(),
        'output' => ''
    ]);
}
?>
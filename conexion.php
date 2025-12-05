<?php
$host = "localhost";      // Servidor
$usuario = "root";        // Usuario de la BD
$clave = "root";              // Contraseña
$bd = "para_xml";         // Nombre de la base de datos

$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conexion->connect_errno) {
    echo "Error al conectar a la base de datos. Código: " . $conexion->connect_errno . 
         " - Detalle: " . $conexion->connect_error;
    exit();
}



?>

<?php
// Datos de conexión
$host = "localhost";      // Servidor
$usuario = "root";        // Usuario de MySQL
$contrasena = "";         // Contraseña (vacía por defecto en XAMPP y Laragon)
$base_datos = "TodaModaMasayaWeb"; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die(" Error de conexión: " . $conexion->connect_error);
} else {
    // echo "Conexión exitosa a la base de datos"; // Puedes descomentar esto para probar
}
?>

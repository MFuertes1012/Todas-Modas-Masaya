<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

/* Puedes mostrar nombre + apellido para identificar mejor */
$sql = "SELECT Cedula, CONCAT(Nombre,' ',Apellido) AS NombreCompleto
        FROM Empleado
        ORDER BY Nombre, Apellido";
$res = $conexion->query($sql);
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);

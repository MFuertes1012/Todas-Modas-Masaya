<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$sql = "SELECT e.Cedula, e.Nombre, e.Apellido, e.Direccion, e.Telefono, e.IdCargo, c.Nombre AS Cargo
        FROM Empleado e
        JOIN Cargo c ON c.IdCargo = e.IdCargo
        ORDER BY e.Apellido, e.Nombre";
$res = $conexion->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);

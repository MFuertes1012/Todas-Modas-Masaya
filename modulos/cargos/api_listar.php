<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$res = $conexion->query("SELECT IdCargo, Nombre FROM Cargo ORDER BY Nombre");
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);

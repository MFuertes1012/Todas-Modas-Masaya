<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdRol = intval($_GET['IdRol'] ?? 0);
if ($IdRol <= 0) { echo json_encode([]); exit; }

$sql = "SELECT IdPermiso FROM RolPermiso WHERE IdRol = $IdRol AND Permitido = 1";
$res = $conexion->query($sql);
$out = [];
while ($r = $res->fetch_assoc()) $out[] = intval($r['IdPermiso']);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);

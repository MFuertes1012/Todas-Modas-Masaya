<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$sql = "SELECT IdPermiso, Grupo, Nombre, Clave FROM Permiso ORDER BY Grupo, Nombre";
$res = $conexion->query($sql);
$grupos = [];
while ($r = $res->fetch_assoc()) {
  $grupos[$r['Grupo']][] = $r;  // agrupamos por Grupo
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($grupos, JSON_UNESCAPED_UNICODE);

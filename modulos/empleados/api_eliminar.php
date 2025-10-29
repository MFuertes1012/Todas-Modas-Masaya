<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Cedula = trim($_POST['Cedula'] ?? '');
if ($Cedula===''){ echo json_encode(['ok'=>false,'msg'=>'Cédula requerida']); exit; }

$stmt = $conexion->prepare("DELETE FROM Empleado WHERE Cedula=?");
$stmt->bind_param("s", $Cedula);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo eliminar (¿referencias?)']);


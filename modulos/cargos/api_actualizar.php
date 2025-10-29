<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCargo = intval($_POST['IdCargo'] ?? 0);
$Nombre  = trim($_POST['Nombre'] ?? '');

if ($IdCargo<=0 || $Nombre===''){ echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit; }

$stmt = $conexion->prepare("UPDATE Cargo SET Nombre=? WHERE IdCargo=?");
$stmt->bind_param("si", $Nombre, $IdCargo);
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo actualizar']);

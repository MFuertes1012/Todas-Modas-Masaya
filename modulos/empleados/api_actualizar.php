<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$original = trim($_POST['originalCedula'] ?? '');
$Cedula    = trim($_POST['Cedula'] ?? '');
$IdCargo   = intval($_POST['IdCargo'] ?? 0);
$Nombre    = trim($_POST['Nombre'] ?? '');
$Apellido  = trim($_POST['Apellido'] ?? '');
$Direccion = trim($_POST['Direccion'] ?? '');
$Telefono  = trim($_POST['Telefono'] ?? '');

if ($original==='' || $Cedula==='' || $IdCargo<=0 || $Nombre==='' || $Apellido==='') {
  echo json_encode(['ok'=>false,'msg'=>'Campos obligatorios faltantes']); exit;
}

$stmt = $conexion->prepare("UPDATE Empleado
  SET Cedula=?, IdCargo=?, Nombre=?, Apellido=?, Direccion=?, Telefono=?
  WHERE Cedula=?");
$stmt->bind_param("sisssss", $Cedula, $IdCargo, $Nombre, $Apellido, $Direccion, $Telefono, $original);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo actualizar']);

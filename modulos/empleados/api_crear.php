<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Cedula    = trim($_POST['Cedula'] ?? '');
$IdCargo   = intval($_POST['IdCargo'] ?? 0);
$Nombre    = trim($_POST['Nombre'] ?? '');
$Apellido  = trim($_POST['Apellido'] ?? '');
$Direccion = trim($_POST['Direccion'] ?? '');
$Telefono  = trim($_POST['Telefono'] ?? '');

if ($Cedula==='' || $IdCargo<=0 || $Nombre==='' || $Apellido==='') {
  echo json_encode(['ok'=>false,'msg'=>'Campos obligatorios faltantes']); exit;
}

$stmt = $conexion->prepare("INSERT INTO Empleado (Cedula, IdCargo, Nombre, Apellido, Direccion, Telefono)
                            VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissss", $Cedula, $IdCargo, $Nombre, $Apellido, $Direccion, $Telefono);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo crear (¿cédula duplicada?)']);

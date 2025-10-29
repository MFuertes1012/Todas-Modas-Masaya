<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Cedula   = trim($_POST['Cedula'] ?? '');
$Usuario  = trim($_POST['Nombre_de_Usuario'] ?? '');
$Correo   = trim($_POST['Correo'] ?? '');
$PassRaw  = trim($_POST['Contrasena'] ?? '');
$IdRol    = intval($_POST['IdRol'] ?? 0);

if ($Cedula==='' || $Usuario==='' || $PassRaw==='' || $IdRol<=0) {
  echo json_encode(['ok'=>false,'msg'=>'Complete los campos requeridos']); exit;
}

/* MD5 por compatibilidad con tu login actual */
$Pass = md5($PassRaw);

/* Validaciones de unicidad (usuario, correo opcional, cédula) */
$stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Nombre_de_Usuario=?");
$stmt->bind_param("s", $Usuario); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'El usuario ya existe']); exit; }

if ($Correo!==''){
  $stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Correo=?");
  $stmt->bind_param("s", $Correo); $stmt->execute(); $stmt->store_result();
  if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'El correo ya está en uso']); exit; }
}

$stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Cedula=?");
$stmt->bind_param("s", $Cedula); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'La cédula ya tiene usuario']); exit; }

$stmt = $conexion->prepare("INSERT INTO Usuario (Cedula, Nombre_de_Usuario, Correo, Contrasena, IdRol)
                            VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $Cedula, $Usuario, $Correo, $Pass, $IdRol);
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo crear']);

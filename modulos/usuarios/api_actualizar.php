<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdUsuario = intval($_POST['IdUsuario'] ?? 0);
$Cedula    = trim($_POST['Cedula'] ?? '');
$Usuario   = trim($_POST['Nombre_de_Usuario'] ?? '');
$Correo    = trim($_POST['Correo'] ?? '');
$PassRaw   = trim($_POST['Contrasena'] ?? ''); // opcional
$IdRol     = intval($_POST['IdRol'] ?? 0);

if ($IdUsuario<=0 || $Cedula==='' || $Usuario==='' || $IdRol<=0) {
  echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit;
}

/* Unicidad excluyendo el propio registro */
$stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Nombre_de_Usuario=? AND IdUsuario<>?");
$stmt->bind_param("si", $Usuario, $IdUsuario); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'El usuario ya existe']); exit; }

if ($Correo!==''){
  $stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Correo=? AND IdUsuario<>?");
  $stmt->bind_param("si", $Correo, $IdUsuario); $stmt->execute(); $stmt->store_result();
  if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'El correo ya está en uso']); exit; }
}

/* Cédula única (un empleado solo un usuario) */
$stmt = $conexion->prepare("SELECT 1 FROM Usuario WHERE Cedula=? AND IdUsuario<>?");
$stmt->bind_param("si", $Cedula, $IdUsuario); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows>0){ echo json_encode(['ok'=>false,'msg'=>'La cédula ya tiene usuario']); exit; }

if ($PassRaw!==''){ // actualizar con nueva contraseña
  $Pass = md5($PassRaw);
  $stmt = $conexion->prepare("UPDATE Usuario
                              SET Cedula=?, Nombre_de_Usuario=?, Correo=?, Contrasena=?, IdRol=?
                              WHERE IdUsuario=?");
  $stmt->bind_param("ssssii", $Cedula, $Usuario, $Correo, $Pass, $IdRol, $IdUsuario);
} else {           // mantener contraseña
  $stmt = $conexion->prepare("UPDATE Usuario
                              SET Cedula=?, Nombre_de_Usuario=?, Correo=?, IdRol=?
                              WHERE IdUsuario=?");
  $stmt->bind_param("sssii", $Cedula, $Usuario, $Correo, $IdRol, $IdUsuario);
}
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo actualizar']);

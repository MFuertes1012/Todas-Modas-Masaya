<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Nombre = trim($_POST['Nombre'] ?? '');
if ($Nombre === '') { echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }

$stmt = $conexion->prepare("INSERT INTO Cargo (Nombre) VALUES (?)");
$stmt->bind_param("s", $Nombre);
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo crear']);

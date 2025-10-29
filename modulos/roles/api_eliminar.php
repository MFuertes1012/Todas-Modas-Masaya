<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdRol = intval($_POST['IdRol'] ?? 0);
if ($IdRol<=0){ echo json_encode(['ok'=>false,'msg'=>'Id inválido']); exit; }

$stmt = $conexion->prepare("DELETE FROM Rol WHERE IdRol=?");
$stmt->bind_param("i", $IdRol);
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo eliminar (¿referencias en Usuario?)']);

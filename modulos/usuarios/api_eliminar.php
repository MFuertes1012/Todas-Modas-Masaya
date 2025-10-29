<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdUsuario = intval($_POST['IdUsuario'] ?? 0);
if ($IdUsuario<=0){ echo json_encode(['ok'=>false,'msg'=>'Id inválido']); exit; }

$stmt = $conexion->prepare("DELETE FROM Usuario WHERE IdUsuario=?");
$stmt->bind_param("i", $IdUsuario);
$ok = $stmt->execute();

echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo eliminar']);

<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Descripcion = trim($_POST['Descripcion'] ?? '');
$permisos = $_POST['permisos'] ?? []; // array de IdPermiso

if ($Descripcion === '') { echo json_encode(['ok'=>false,'msg'=>'Descripción requerida']); exit; }

$conexion->begin_transaction();
try {
  $stmt = $conexion->prepare("INSERT INTO Rol (Descripcion) VALUES (?)");
  $stmt->bind_param("s", $Descripcion);
  $stmt->execute();
  $nuevoId = $stmt->insert_id;

  if (!empty($permisos) && is_array($permisos)) {
    $ins = $conexion->prepare("INSERT INTO RolPermiso (IdRol, IdPermiso, Permitido) VALUES (?, ?, 1)");
    foreach ($permisos as $p) {
      $pid = intval($p);
      $ins->bind_param("ii", $nuevoId, $pid);
      $ins->execute();
    }
  }

  $conexion->commit();
  echo json_encode(['ok'=>true, 'IdRol'=>$nuevoId]);
} catch (Throwable $e) {
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>'No se pudo crear']);
}

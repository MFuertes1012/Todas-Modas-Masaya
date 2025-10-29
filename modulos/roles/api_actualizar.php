<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdRol = intval($_POST['IdRol'] ?? 0);
$Descripcion = trim($_POST['Descripcion'] ?? '');
$permisos = $_POST['permisos'] ?? []; // array

if ($IdRol<=0 || $Descripcion===''){ echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit; }

$conexion->begin_transaction();
try {
  $stmt = $conexion->prepare("UPDATE Rol SET Descripcion=? WHERE IdRol=?");
  $stmt->bind_param("si", $Descripcion, $IdRol);
  $stmt->execute();

  // Reset permisos y volver a insertar solo los seleccionados
  $conexion->query("DELETE FROM RolPermiso WHERE IdRol=$IdRol");

  if (!empty($permisos) && is_array($permisos)) {
    $ins = $conexion->prepare("INSERT INTO RolPermiso (IdRol, IdPermiso, Permitido) VALUES (?, ?, 1)");
    foreach ($permisos as $p) {
      $pid = intval($p);
      $ins->bind_param("ii", $IdRol, $pid);
      $ins->execute();
    }
  }

  $conexion->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>'No se pudo actualizar']);
}

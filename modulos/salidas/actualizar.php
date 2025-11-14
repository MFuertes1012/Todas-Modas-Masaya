<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $IdVenta   = (int)($_POST['IdVenta'] ?? 0);
  $IdCliente = (int)($_POST['IdCliente'] ?? 0);
  $Fecha     = $_POST['Fecha'] ?? date('Y-m-d');
  $Metodo    = trim($_POST['Metodo_de_pago'] ?? 'Efectivo');

  if ($IdVenta<=0 || $IdCliente<=0) { echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit; }

  $conexion->begin_transaction();

  // Devolver stock anterior
  $rs = $conexion->query("SELECT IdProducto, Cantidad FROM Detalle_de_salida WHERE IdVenta=$IdVenta FOR UPDATE");
  while ($row = $rs->fetch_assoc()) {
    $conexion->query("UPDATE Producto SET Cantidad = Cantidad + ".(int)$row['Cantidad']." WHERE IdProducto=".(int)$row['IdProducto']);
  }
  $conexion->query("DELETE FROM Detalle_de_salida WHERE IdVenta=$IdVenta");

  $st = $conexion->prepare("UPDATE Salida_de_stock SET IdCliente=?, Fecha=?, Metodo_de_pago=? WHERE IdVenta=?");
  $st->bind_param("issi", $IdCliente, $Fecha, $Metodo, $IdVenta);
  $st->execute();

  // 3Insertar nuevo detalle con control de stock
  $IdProducto = $_POST['IdProducto'] ?? [];
  $Cant       = $_POST['Cantidad'] ?? [];

  $stSel = $conexion->prepare("SELECT Cantidad, Precio_de_Venta FROM Producto WHERE IdProducto=? FOR UPDATE");
  $stUpd = $conexion->prepare("UPDATE Producto SET Cantidad = Cantidad - ? WHERE IdProducto=?");
  $stDet = $conexion->prepare("INSERT INTO Detalle_de_salida (IdVenta, IdProducto, Cantidad, PrecioUnitario) VALUES (?,?,?,?)");

  for ($i=0; $i<count($IdProducto); $i++) {
    $p = (int)$IdProducto[$i];
    $req = max(1, (int)$Cant[$i]);

    $stSel->bind_param("i", $p); $stSel->execute();
    $row = $stSel->get_result()->fetch_assoc();
    if (!$row) throw new Exception("Producto $p no existe");

    $stock = (int)$row['Cantidad'];
    $precio = (float)$row['Precio_de_Venta'];
    $qty = min($req, $stock);

    if ($qty <= 0) continue;

    $stUpd->bind_param("ii", $qty, $p);  $stUpd->execute();
    $stDet->bind_param("iiid", $IdVenta, $p, $qty, $precio); $stDet->execute();
  }

  $conexion->commit();
  echo json_encode(['ok'=>true,'msg'=>'Salida actualizada']);

} catch (Exception $e) {
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
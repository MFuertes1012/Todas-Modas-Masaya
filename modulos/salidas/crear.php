<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getUserId(mysqli $cx) {
  if (!empty($_SESSION['IdUsuario'])) return (int)$_SESSION['IdUsuario'];
  if (empty($_SESSION['correo'])) return 0;
  $st = $cx->prepare("SELECT IdUsuario FROM Usuario WHERE Correo = ? LIMIT 1");
  $st->bind_param("s", $_SESSION['correo']); $st->execute();
  $r = $st->get_result()->fetch_assoc();
  return $r ? (int)$r['IdUsuario'] : 0;
}

try {
  $IdUsuario  = getUserId($conexion);
  $IdCliente  = intval($_POST['IdCliente'] ?? 0);
  $Fecha      = $_POST['Fecha'] ?? date('Y-m-d');
  $Metodo     = trim($_POST['Metodo_de_pago'] ?? 'Efectivo');

  $IdProducto = $_POST['IdProducto'] ?? [];
  $Cantidades = $_POST['Cantidad'] ?? [];
  $Precios    = $_POST['PrecioUnitario'] ?? [];

  if ($IdUsuario<=0 || $IdCliente<=0) { echo json_encode(['ok'=>false,'msg'=>'Usuario o cliente inválido']); exit; }
  if (!count($IdProducto)) { echo json_encode(['ok'=>false,'msg'=>'Detalle vacío']); exit; }

  $conexion->begin_transaction();

  $st = $conexion->prepare("INSERT INTO Salida_de_stock (IdUsuario, IdCliente, Fecha, Metodo_de_pago) VALUES (?,?,?,?)");
  $st->bind_param("iiss", $IdUsuario, $IdCliente, $Fecha, $Metodo);
  $st->execute();
  $IdVenta = $conexion->insert_id;

  $stSel = $conexion->prepare("SELECT Cantidad, Precio_de_Venta FROM Producto WHERE IdProducto=? FOR UPDATE");
  $stUpd = $conexion->prepare("UPDATE Producto SET Cantidad = Cantidad - ? WHERE IdProducto=?");
  $stDet = $conexion->prepare("INSERT INTO Detalle_de_salida (IdVenta, IdProducto, Cantidad, PrecioUnitario) VALUES (?,?,?,?)");

  for ($i=0; $i<count($IdProducto); $i++) {
    $p = (int)$IdProducto[$i];
    $req = max(1, (int)$Cantidades[$i]);

    $stSel->bind_param("i", $p); $stSel->execute();
    $row = $stSel->get_result()->fetch_assoc();
    if (!$row) throw new Exception("Producto $p no existe");

    $stock = (int)$row['Cantidad'];
    $precioVenta = (float)$row['Precio_de_Venta'];

    $qty = min($req, $stock);          
    if ($qty <= 0) continue;            

    $stUpd->bind_param("ii", $qty, $p);  $stUpd->execute();
    $stDet->bind_param("iiid", $IdVenta, $p, $qty, $precioVenta); $stDet->execute();
  }

  $conexion->commit();
  echo json_encode(['ok'=>true,'msg'=>'Salida registrada','IdVenta'=>$IdVenta]);

} catch (Exception $e) {
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
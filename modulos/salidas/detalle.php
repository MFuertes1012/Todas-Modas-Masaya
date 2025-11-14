<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $IdVenta = (int)($_GET['IdVenta'] ?? ($_POST['IdVenta'] ?? 0));
  if ($IdVenta<=0) { echo json_encode(['ok'=>false,'msg'=>'Id inválido']); exit; }

  $sql = "SELECT s.IdVenta, s.Fecha, s.Metodo_de_pago,
                 u.Nombre_de_Usuario AS Vendedor,
                 CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                 d.IdDetVenta, d.IdProducto, pr.Nombre AS Producto,
                 pr.Marca, pr.Talla, d.Cantidad, d.PrecioUnitario, d.Subtotal
          FROM Salida_de_stock s
          JOIN Usuario u ON u.IdUsuario = s.IdUsuario
          JOIN Cliente c ON c.IdCliente = s.IdCliente
          JOIN Detalle_de_salida d ON d.IdVenta = s.IdVenta
          JOIN Producto pr ON pr.IdProducto = d.IdProducto
          WHERE s.IdVenta = ?
          ORDER BY d.IdDetVenta ASC";
  $st = $conexion->prepare($sql);
  $st->bind_param("i", $IdVenta);
  $st->execute();
  $res = $st->get_result();

  $cab = null; $det = []; $total = 0;
  while ($row = $res->fetch_assoc()) {
    if ($cab===null) {
      $cab = [
        'IdVenta' => $row['IdVenta'],
        'Fecha' => $row['Fecha'],
        'Metodo' => $row['Metodo_de_pago'],
        'Vendedor' => $row['Vendedor'],
        'Cliente' => $row['Cliente'],
      ];
    }
    $det[] = [
      'IdDetVenta' => $row['IdDetVenta'],
      'IdProducto' => $row['IdProducto'],
      'Producto'   => $row['Producto'],
      'Marca'      => $row['Marca'],
      'Talla'      => $row['Talla'],
      'Cantidad'   => $row['Cantidad'],
      'PrecioUnitario' => $row['PrecioUnitario'],
      'Subtotal'       => $row['Subtotal'],
    ];
    $total += (float)$row['Subtotal'];
  }
  if (!$cab) { echo json_encode(['ok'=>false,'msg'=>'No encontrada']); exit; }

  echo json_encode(['ok'=>true,'cabecera'=>$cab,'detalles'=>$det,'total'=>$total], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
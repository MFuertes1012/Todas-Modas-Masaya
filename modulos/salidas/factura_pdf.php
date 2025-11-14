<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
require_once dirname(__DIR__, 2) . '/conexion.php';
require_once dirname(__DIR__, 2) . '/tcpdf/tcpdf.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$IdVenta = (int)($_GET['IdVenta'] ?? 0);
if ($IdVenta<=0) die('ID inválido');

$sqlCab = "SELECT s.IdVenta, s.Fecha, s.Metodo_de_pago,
                  u.Nombre_de_Usuario AS Vendedor,
                  CONCAT(c.Nombre,' ',c.Apellido) AS Cliente
           FROM Salida_de_stock s
           JOIN Usuario u ON u.IdUsuario = s.IdUsuario
           JOIN Cliente c ON c.IdCliente = s.IdCliente
           WHERE s.IdVenta = ?";
$st = $conexion->prepare($sqlCab);
$st->bind_param("i",$IdVenta);
$st->execute();
$cab = $st->get_result()->fetch_assoc();
if (!$cab) die('No encontrada');

$sqlDet = "SELECT pr.Nombre AS Producto, pr.Marca, pr.Talla,
                  d.Cantidad, d.PrecioUnitario, d.Subtotal
           FROM Detalle_de_salida d
           JOIN Producto pr ON pr.IdProducto = d.IdProducto
           WHERE d.IdVenta = ?";
$st2 = $conexion->prepare($sqlDet);
$st2->bind_param("i",$IdVenta);
$st2->execute();
$rs = $st2->get_result();

$total = 0; $items = [];
while ($row = $rs->fetch_assoc()) { $total += $row['Subtotal']; $items[] = $row; }

$pdf = new TCPDF();
$pdf->SetCreator('Toda Moda Masaya');
$pdf->SetAuthor('Sistema Web');
$pdf->SetTitle('Factura de salida #'.$IdVenta);
$pdf->SetMargins(20,25,20);
$pdf->AddPage();

$html = '<h2 style="text-align:center;color:#e014ca;">Factura de salida</h2><hr>
<table border="0" cellspacing="2" cellpadding="3">
<tr><td><strong>ID Venta:</strong></td><td>'.$cab['IdVenta'].'</td></tr>
<tr><td><strong>Fecha:</strong></td><td>'.$cab['Fecha'].'</td></tr>
<tr><td><strong>Vendedor:</strong></td><td>'.$cab['Vendedor'].'</td></tr>
<tr><td><strong>Cliente:</strong></td><td>'.$cab['Cliente'].'</td></tr>
<tr><td><strong>Método de pago:</strong></td><td>'.$cab['Metodo_de_pago'].'</td></tr>
</table><br>
<h4>Detalle</h4>
<table border="1" cellpadding="4">
<thead><tr style="background:#fce4ec;">
  <th><b>Producto</b></th><th><b>Marca</b></th><th><b>Talla</b></th>
  <th><b>Cantidad</b></th><th><b>Precio (C$)</b></th><th><b>Subtotal (C$)</b></th>
</tr></thead><tbody>';
foreach ($items as $it){
  $html.='<tr>
    <td>'.htmlspecialchars($it['Producto']).'</td>
    <td>'.htmlspecialchars($it['Marca']).'</td>
    <td>'.htmlspecialchars($it['Talla']).'</td>
    <td align="center">'.$it['Cantidad'].'</td>
    <td align="right">'.number_format($it['PrecioUnitario'],2).'</td>
    <td align="right">'.number_format($it['Subtotal'],2).'</td>
  </tr>';
}
$html.='</tbody></table><br>
<h3 style="text-align:right;">Total: C$ '.number_format($total,2).'</h3>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Factura_Salida_'.$IdVenta.'.pdf','I');
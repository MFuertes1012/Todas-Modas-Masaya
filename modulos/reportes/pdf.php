<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    exit('No autorizado');
}

require_once dirname(__DIR__, 2) . '/conexion.php';
require_once dirname(__DIR__, 2) . '/tcpdf/tcpdf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$tipo  = $_GET['tipo']  ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$categoria = $_GET['categoria'] ?? '';
$producto  = $_GET['producto']  ?? '';
$proveedor = $_GET['proveedor'] ?? '';
$cedula    = $_GET['cedula']    ?? '';
$cliente   = $_GET['cliente']   ?? '';

if ($desde === '' && $hasta === '') {
    $desde = '1900-01-01';
    $hasta = date('Y-m-d');
} elseif ($desde === '' && $hasta !== '') {
    $desde = '1900-01-01';
} elseif ($desde !== '' && $hasta === '') {
    $hasta = date('Y-m-d');
}

$cols = [];
$rows = [];
$titulo = '';

try {

    switch ($tipo) {
        case 'inventario':
            $titulo = 'Reporte de Inventario';
            $cols = [
                'ID Producto','Nombre','Categoría',
                'Cant. Comprada','Cant. disponible','Cant. vendida',
                'Precio venta (C$)','Total ganado (C$)'
            ];
            $sql = "
              SELECT 
                p.IdProducto,
                p.Nombre,
                c.Descripcion AS Categoria,
                IFNULL(comp.CantComprada,0) AS CantComprada,
                p.Cantidad AS CantDisponible,
                IFNULL(vent.CantVendida,0) AS CantVendida,
                p.Precio_de_Venta,
                IFNULL(vent.TotalVendido,0) AS TotalGanado
              FROM Producto p
              JOIN Categoria c ON c.IdCategoria = p.IdCategoria
              LEFT JOIN (
                SELECT dc.IdProducto, SUM(dc.Cantidad) AS CantComprada
                FROM Detalle_Compra dc
                JOIN Compra co ON co.IdCompra = dc.IdCompra
                WHERE co.Fecha BETWEEN ? AND ?
                GROUP BY dc.IdProducto
              ) comp ON comp.IdProducto = p.IdProducto
              LEFT JOIN (
                SELECT ds.IdProducto, SUM(ds.Cantidad) AS CantVendida, SUM(ds.Subtotal) AS TotalVendido
                FROM Detalle_de_salida ds
                JOIN Salida_de_stock s ON s.IdVenta = ds.IdVenta
                WHERE s.Fecha BETWEEN ? AND ?
                GROUP BY ds.IdProducto
              ) vent ON vent.IdProducto = p.IdProducto
              WHERE 1=1
            ";

            $types  = "ssss";
            $params = [$desde, $hasta, $desde, $hasta];

            if ($categoria !== '') {
                $sql   .= " AND p.IdCategoria = ? ";
                $types .= "i";
                $params[] = (int)$categoria;
            }
            if ($producto !== '') {
                $sql   .= " AND p.IdProducto = ? ";
                $types .= "i";
                $params[] = (int)$producto;
            }

            $sql .= " ORDER BY p.IdProducto ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['IdProducto'],
                    $row['Nombre'],
                    $row['Categoria'],
                    $row['CantComprada'],
                    $row['CantDisponible'],
                    $row['CantVendida'],
                    number_format($row['Precio_de_Venta'], 2),
                    number_format($row['TotalGanado'], 2),
                ];
            }
            break;

        case 'compras':
            $titulo = 'Reporte de Compras';
            $cols = [
                'Comprado por','Fecha','Proveedor',
                'Producto','Categoría','Cant. comprada',
                'Precio compra (C$)','Total compra (C$)'
            ];
            $sql = "
              SELECT
                u.Nombre_de_Usuario AS Comprador,
                co.Fecha,
                pr.Nombre AS Proveedor,
                p.Nombre  AS Producto,
                cat.Descripcion AS Categoria,
                dc.Cantidad,
                dc.PrecioUnitario,
                dc.Subtotal
              FROM Compra co
              JOIN Usuario u         ON u.IdUsuario    = co.IdUsuario
              JOIN Proveedor pr      ON pr.IdProveedor = co.IdProveedor
              JOIN Detalle_Compra dc ON dc.IdCompra    = co.IdCompra
              JOIN Producto p        ON p.IdProducto   = dc.IdProducto
              JOIN Categoria cat     ON cat.IdCategoria = p.IdCategoria
              WHERE co.Fecha BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($proveedor !== '') {
                $sql   .= " AND co.IdProveedor = ? ";
                $types .= "i";
                $params[] = (int)$proveedor;
            }
            if ($producto !== '') {
                $sql   .= " AND p.IdProducto = ? ";
                $types .= "i";
                $params[] = (int)$producto;
            }

            $sql .= " ORDER BY co.Fecha ASC, co.IdCompra ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Comprador'],
                    $row['Fecha'],
                    $row['Proveedor'],
                    $row['Producto'],
                    $row['Categoria'],
                    $row['Cantidad'],
                    number_format($row['PrecioUnitario'], 2),
                    number_format($row['Subtotal'], 2),
                ];
            }
            break;

        case 'empleados':
            $titulo = 'Reporte de Nómina / Empleados';
            $cols = [
                'Cédula',
                'Nombre y Apellidos',
                'Cargo',
                'Salario básico (C$)',
                'Salario bruto (C$)',
                'Deducción total (C$)',
                'Salario neto (C$)'
            ];

            $sql = "
              SELECT
                n.Cedula,
                CONCAT(e.Nombre, ' ', e.Apellido) AS NombreCompleto,
                c.Nombre AS Cargo,
                n.SalarioBasico,
                n.SalarioBruto,
                n.DeduccionTotal,
                n.SalarioNeto
              FROM Nomina n
              JOIN Empleado e ON e.Cedula  = n.Cedula
              JOIN Cargo    c ON c.IdCargo = e.IdCargo
              WHERE n.FechaRegistro BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($cedula !== '') {
                $sql   .= " AND n.Cedula = ? ";
                $types .= "s";
                $params[] = $cedula;
            }

            $sql .= " ORDER BY n.FechaRegistro ASC, n.IdNomina ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();

            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Cedula'],
                    $row['NombreCompleto'],
                    $row['Cargo'],
                    number_format($row['SalarioBasico'], 2),
                    number_format($row['SalarioBruto'], 2),
                    number_format($row['DeduccionTotal'], 2),
                    number_format($row['SalarioNeto'], 2),
                ];
            }
            break;

        case 'salidas':
            $titulo = 'Reporte de Salida de inventario (Ventas)';
            $cols = [
                'Realizado por','Fecha','Cliente',
                'Método de pago','Cant. productos','Total venta (C$)'
            ];
            $sql = "
              SELECT
                u.Nombre_de_Usuario AS Vendedor,
                s.Fecha,
                CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                s.Metodo_de_pago,
                SUM(ds.Cantidad) AS CantProductos,
                SUM(ds.Subtotal) AS TotalVenta
              FROM Salida_de_stock s
              JOIN Usuario u ON u.IdUsuario = s.IdUsuario
              JOIN Cliente c ON c.IdCliente = s.IdCliente
              JOIN Detalle_de_salida ds ON ds.IdVenta = s.IdVenta
              WHERE s.Fecha BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($cliente !== '') {
                $sql   .= " AND s.IdCliente = ? ";
                $types .= "i";
                $params[] = (int)$cliente;
            }

            $sql .= "
              GROUP BY s.IdVenta
              ORDER BY s.Fecha ASC, s.IdVenta ASC
            ";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Vendedor'],
                    $row['Fecha'],
                    $row['Cliente'],
                    $row['Metodo_de_pago'],
                    $row['CantProductos'],
                    number_format($row['TotalVenta'], 2),
                ];
            }
            break;

        default:
            throw new Exception('Tipo de reporte inválido');
    }

    $pdf = new TCPDF();
    $pdf->SetCreator('Toda Moda Masaya');
    $pdf->SetAuthor('Sistema Web');
    $pdf->SetTitle($titulo);
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    $html = '<h2 style="text-align:center;">'.$titulo.'</h2>';
    $html .= '<p><strong>Desde:</strong> '.htmlspecialchars($desde).' &nbsp;&nbsp; ';
    $html .= '<strong>Hasta:</strong> '.htmlspecialchars($hasta).'</p>';

    $html .= '<table border="1" cellspacing="0" cellpadding="4"><thead><tr>';
    foreach ($cols as $c) {
        $html .= '<th><b>'.htmlspecialchars($c).'</b></th>';
    }
    $html .= '</tr></thead><tbody>';

    if (empty($rows)) {
        $html .= '<tr><td colspan="'.count($cols).'">No hay datos para el rango seleccionado.</td></tr>';
    } else {
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>'.htmlspecialchars((string)$cell).'</td>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('reporte_'.$tipo.'.pdf', 'I');

} catch (Exception $e) {
    echo 'Error generando PDF: '.$e->getMessage();
}

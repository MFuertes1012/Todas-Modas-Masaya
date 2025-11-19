<?php
session_start();
if (!isset($_SESSION['correo'])) {
  http_response_code(401);
  exit('No autorizado');
}

require_once dirname(__DIR__, 2) . '/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


$categorias  = [];
$productos   = [];
$proveedores = [];
$empleados   = [];
$clientes    = [];

try {
  $res = $conexion->query("SELECT IdCategoria, Descripcion FROM Categoria ORDER BY Descripcion ASC");
  while ($row = $res->fetch_assoc()) $categorias[] = $row;

  $res = $conexion->query("SELECT IdProducto, Nombre FROM Producto ORDER BY Nombre ASC");
  while ($row = $res->fetch_assoc()) $productos[] = $row;

  $res = $conexion->query("SELECT IdProveedor, Nombre FROM Proveedor ORDER BY Nombre ASC");
  while ($row = $res->fetch_assoc()) $proveedores[] = $row;

  $res = $conexion->query("SELECT Cedula, Nombre, Apellido FROM Empleado ORDER BY Nombre ASC, Apellido ASC");
  while ($row = $res->fetch_assoc()) $empleados[] = $row;

  $res = $conexion->query("SELECT IdCliente, Nombre, Apellido FROM Cliente ORDER BY Nombre ASC, Apellido ASC");
  while ($row = $res->fetch_assoc()) $clientes[] = $row;

} catch (Exception $e) {

}
?>
<div class="vistas-wrapper">

  <h2 class="titulo-principal">Reportes</h2>

  <div class="tabla-card">

    <!-- Filtros -->
    <div class="card-actions reportes-filtros">
      <select id="tipoReporte" class="input-select">
        <option value="inventario">Inventario</option>
        <option value="compras">Compras</option>
        <option value="empleados">Nomina</option>
        <option value="salidas">Salida de inventario</option>
      </select>

      <input type="date" id="repDesde" class="input-date">
      <span class="rango-separador">a</span>
      <input type="date" id="repHasta" class="input-date">

      <!-- Filtros  dinámicos -->
      <select id="filtroCategoria" class="input-select filtro-extra" style="display:none;">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= (int)$c['IdCategoria'] ?>">
            <?= htmlspecialchars($c['Descripcion']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select id="filtroProducto" class="input-select filtro-extra" style="display:none;">
        <option value="">Todos los productos</option>
        <?php foreach ($productos as $p): ?>
          <option value="<?= (int)$p['IdProducto'] ?>">
            <?= htmlspecialchars($p['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select id="filtroProveedor" class="input-select filtro-extra" style="display:none;">
        <option value="">Todos los proveedores</option>
        <?php foreach ($proveedores as $p): ?>
          <option value="<?= (int)$p['IdProveedor'] ?>">
            <?= htmlspecialchars($p['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select id="filtroCedula" class="input-select filtro-extra" style="display:none;">
        <option value="">Todas los empleados</option>
        <?php foreach ($empleados as $e): ?>
          <option value="<?= htmlspecialchars($e['Cedula']) ?>">
            <?= htmlspecialchars($e['Cedula'] . ' - ' . $e['Nombre'] . ' ' . $e['Apellido']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select id="filtroCliente" class="input-select filtro-extra" style="display:none;">
        <option value="">Todos los clientes</option>
        <?php foreach ($clientes as $c): ?>
          <option value="<?= (int)$c['IdCliente'] ?>">
            <?= htmlspecialchars($c['Nombre'] . ' ' . $c['Apellido']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="button" id="btnVerReporte" class="btn-nuevo">
        Ver datos
      </button>

      <button type="button" id="btnPdfReporte" class="btn-descargar">
        Descargar PDF
      </button>
    </div>

   
    <h3 class="preview-title">Vista previa</h3>
    <div class="preview-box">
      <table id="tablaReporte">
        <thead></thead>
        <tbody>
          <tr><td>No hay datos para mostrar.</td></tr>
        </tbody>
      </table>
    </div>

  </div>
</div>

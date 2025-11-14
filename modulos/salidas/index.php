<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
require_once dirname(__DIR__, 2) . '/conexion.php';

/* == Clientes para el cbx == */
$clientes = [];
$r = $conexion->query("SELECT IdCliente, CONCAT(Nombre,' ',Apellido) AS Nombre FROM Cliente ORDER BY Nombre");
if ($r) while ($row = $r->fetch_assoc()) $clientes[] = $row;

/* == Productos para el detalle (con marca, talla, precio, stock) == */
$productos = [];
$sqlProd = "SELECT IdProducto, Nombre, Marca, Talla, Precio_de_Venta, Cantidad
            FROM Producto ORDER BY Nombre";
$r2 = $conexion->query($sqlProd);
if ($r2) while ($row = $r2->fetch_assoc()) $productos[] = $row;
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de salidas de stock</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevaSalida" class="btn-nuevo">Nueva salida</button>
    </div>

    <!-- Filtros: texto + rango fechas -->
    <div class="card-actions">
      <input type="date" id="ventaDesde" class="input-date" />
      <span>a</span>
      <input type="date" id="ventaHasta" class="input-date" />
      <input type="text" id="buscarSalida" class="input-buscar"
             placeholder="Buscar por cliente" />
    </div>

    <table class="tabla-vistas" id="tablaSalidas">
      <thead>
        <tr>
          <th style="width:90px;">ID Venta</th>
          <th>Vendedor</th>
          <th>Cliente</th>
          <th>Método de pago</th>
          <th>Fecha</th>
          <th style="width:110px;">Ver factura</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear / Editar -->
 <div class="modal" id="modalSalida" aria-hidden="true">
  <div class="modal-content large">
    <div class="modal-header">
      <h3 id="modalTituloSalida">Nueva salida</h3>
    </div>

    <form id="formSalida" autocomplete="off">
      <input type="hidden" name="IdVenta" id="IdVenta">

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="IdCliente">Cliente</label>
          <select name="IdCliente" id="IdCliente" required>
            <option value="" disabled selected>Seleccione cliente</option>
            <?php foreach ($clientes as $c): ?>
              <option value="<?= htmlspecialchars($c['IdCliente']) ?>">
                <?= htmlspecialchars($c['Nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="FechaVenta">Fecha</label>
          <input type="date" name="Fecha" id="FechaVenta" value="<?= date('Y-m-d'); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-field" style="width:100%;">
          <label>Detalle de productos</label>
          <table class="tabla-vistas tabla-detalle">
            <thead>
              <tr>
                <th style="width:220px;">Producto</th>
                <th style="width:140px;">Marca</th>
                <th style="width:120px;">Talla</th>
                <th style="width:120px;">Cant.</th>
                <th style="width:150px;">Precio Unitario</th>
                <th style="width:140px;">Subtotal</th>
                <th style="width:60px;">Quitar</th>
              </tr>
            </thead>
            <tbody id="detalleBody"></tbody>
          </table>
         <button type="button" class="btn-secundario" id="btnAgregarFila">
            Agregar producto
          </button>
        </div>
      </div>

      <div class="form-row">
        <div class="form-field" style="max-width:260px;">
          <label for="MetodoPago">Método de pago</label>
          <select id="MetodoPago" name="Metodo_de_pago" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
          </select>
        </div>
        <div class="form-field total-field" style="margin-left:auto;">
          <label>Total de la salida</label>
          <input type="text" id="TotalVenta" readonly value="C$ 0.00">
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarSalida">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarSalida">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Template fila detalle -->
<template id="filaDetalleVentaTemplate">
  <tr>
    <td>
      <select name="IdProducto[]" class="sel-producto" required>
        <option value="">Seleccione producto</option>
        <?php foreach ($productos as $p): ?>
          <option
            value="<?= htmlspecialchars($p['IdProducto']) ?>"
            data-marca="<?= htmlspecialchars($p['Marca']) ?>"
            data-talla="<?= htmlspecialchars($p['Talla']) ?>"
            data-precio="<?= htmlspecialchars($p['Precio_de_Venta']) ?>"
            data-stock="<?= htmlspecialchars($p['Cantidad']) ?>"
          >
            <?= htmlspecialchars($p['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><input type="text" class="inp-marca"  name="Marca[]"  readonly></td>
    <td><input type="text" class="inp-talla"  name="Talla[]"  readonly></td>
    <td><input type="number" class="inp-cantidad" name="Cantidad[]" min="1" value="1" required></td>
    <td><input type="number" class="inp-precio"   name="PrecioUnitario[]" step="0.01" min="0" readonly></td>
    <td class="celda-subtotal">C$ 0.00</td>
    <td style="text-align:center;">
      <button type="button" class="btn-remove-row" title="Quitar fila">
        <img src="img/borrar.png" alt="Quitar">
      </button>
    </td>
  </tr>
</template>

<!-- Modal Factura -->
<div class="modal" id="modalFacturaVenta" aria-hidden="true">
  <div class="modal-content large">
    <div class="modal-header">
      <h3>Factura de salida</h3>
    </div>

    <div class="factura-header">
      <p><strong>ID Venta:</strong> <span id="factVId"></span></p>
      <p><strong>Vendedor:</strong> <span id="factVendedor"></span></p>
      <p><strong>Cliente:</strong> <span id="factCliente"></span></p>
      <p><strong>Método de pago:</strong> <span id="factMetodo"></span></p>
      <p><strong>Fecha:</strong> <span id="factVFecha"></span></p>
    </div>

    <table class="tabla-vistas">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Marca</th>
          <th>Talla</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody id="factVentaBody"></tbody>
    </table>

    <div class="factura-total">
      <p><strong>Total:</strong> <span id="factVTotal"></span></p>
    </div>

    <div class="modal-actions">
      <a id="btnDescargarFacturaVenta" href="#" class="btn-guardar" target="_blank">Descargar PDF</a>
      <button type="button" class="btn-cancelar" id="btnCerrarFacturaVenta">Cerrar</button>
    </div>
  </div>
</div>

<div id="toast" class="toast" style="display:none;"></div>
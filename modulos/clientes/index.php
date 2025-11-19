<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }

require_once dirname(__DIR__, 2) . '/conexion.php';


$departamentos = [];
$sqlDep = "SELECT IdDepartamento, Nombre FROM Departamento ORDER BY Nombre";
$resDep = $conexion->query($sqlDep);
if ($resDep) {
  while ($row = $resDep->fetch_assoc()) {
    $departamentos[] = $row;
  }
}
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Clientes</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoCliente" class="btn-nuevo">Nuevo cliente</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarCliente" class="input-buscar"
             placeholder="Buscar por nombre, apellido o teléfono">
    </div>

    <table class="tabla-vistas" id="tablaClientes">
      <thead>
        <tr>
          <th style="width:90px;">ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Departamento</th>
          <th>Dirección</th>
          <th>Teléfono</th>
          <th>Tipo cliente</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear y Editar -->
<div class="modal" id="modalCliente" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloCli">Información del cliente</h3>
    </div>

    <form id="formCliente" autocomplete="off">
      <input type="hidden" name="IdCliente" id="IdCliente">

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/info.svg" alt=""> </div>
        <div class="form-field">
          <label for="Nombre">Nombre</label>
          <input type="text" name="Nombre" id="Nombre" required>
        </div>
      </div>

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/info.svg" alt=""> </div>
        <div class="form-field">
          <label for="Apellido">Apellido</label>
          <input type="text" name="Apellido" id="Apellido" required>
        </div>
      </div>

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/departamento.svg" alt=""> </div>
        <div class="form-field">
          <label for="IdDepartamento">Departamento</label>
          <select name="IdDepartamento" id="IdDepartamento" required>
            <option value="" disabled selected>Seleccione un departamento</option>
            <?php foreach ($departamentos as $d): ?>
              <option value="<?= htmlspecialchars($d['IdDepartamento']) ?>">
                <?= htmlspecialchars($d['Nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/direccion.svg" alt=""> </div>
        <div class="form-field">
          <label for="Direccion">Dirección</label>
          <input type="text" name="Direccion" id="Direccion">
        </div>
      </div>

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/telefono.svg" alt=""> </div>
        <div class="form-field">
          <label for="Telefono">Teléfono</label>
          <input type="text" name="Telefono" id="Telefono">
        </div>
      </div>

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/tipo.svg" alt=""> </div>
        <div class="form-field">
          <label for="TipoCliente">Tipo de cliente</label>
          <input type="text" name="TipoCliente" id="TipoCliente" placeholder="Ej: Frecuente, Mayorista, etc.">
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarCli">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarCli">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!--  eliminar -->
<div class="modal" id="modalConfirmCli" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar este cliente?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoCli">No</button>
      <button type="button" class="btn-eliminar" id="btnSiCli">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>

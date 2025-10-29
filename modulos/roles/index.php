<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
require_once dirname(__DIR__, 2) . '/conexion.php';
?>
<link rel="stylesheet" href="css/empleados.css">

<div class="empleados-wrapper">
  <h2 class="titulo-principal">Lista de Roles</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoRol" class="btn-nuevo">Nuevo rol</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarRol" class="input-buscar" placeholder="Buscar por descripción o ID">
    </div>

    <table class="tabla-empleados" id="tablaRoles">
      <thead>
        <tr>
          <th style="width:120px;">ID</th>
          <th>Descripción</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody><!-- JS --></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear/Editar -->
<div class="modal" id="modalRol" aria-hidden="true">
  <div class="modal-content" style="max-height: 80vh; overflow:auto;">
    <div class="modal-header">
      <h3 id="modalTituloRol">Nuevo rol</h3>
    </div>
    <form id="formRol" autocomplete="off">
      <input type="hidden" id="IdRol" name="IdRol">
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="DescripcionRol">Descripción</label>
          <input type="text" id="DescripcionRol" name="Descripcion" required maxlength="60">
        </div>
      </div>

      <!-- Lista de permisos -->
      <div id="permisosContainer" style="margin-top:12px;">
        <!-- JS renderiza grupos y switches aquí -->
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarRol">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarRol">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirm Eliminar -->
<div class="modal" id="modalConfirmRol" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header">
      <h3>Confirmar eliminación</h3>
    </div>
    <p>¿Está seguro de eliminar este rol?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoRol">No</button>
      <button type="button" class="btn-eliminar" id="btnSiRol">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toastRol" class="toast" style="display:none;"></div>

<script src="js/roles.js?v=<?php echo time(); ?>" defer></script>

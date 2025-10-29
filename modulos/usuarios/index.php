<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
require_once dirname(__DIR__, 2) . '/conexion.php';
?>
<link rel="stylesheet" href="css/empleados.css">

<div class="empleados-wrapper">
  <h2 class="titulo-principal">Lista de Usuarios</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoUsuario" class="btn-nuevo">Nuevo usuario</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarUsuario" class="input-buscar" placeholder="Buscar por cédula, usuario, correo o rol">
    </div>

    <table class="tabla-empleados" id="tablaUsuarios">
      <thead>
        <tr>
          <th style="width:90px;">ID</th>
          <th style="width:200px;">Cédula</th>
          <th>Usuario</th>
          <th>Correo</th>
          <th style="width:160px;">Rol</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody><!-- JS --></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear/Editar -->
<div class="modal" id="modalUsuario" aria-hidden="true">
  <div class="modal-content" style="max-height:80vh;overflow:auto;">
    <div class="modal-header">
      <h3 id="modalTituloUsuario">Nuevo usuario</h3>
    </div>
    <form id="formUsuario" autocomplete="off">
      <input type="hidden" id="IdUsuario" name="IdUsuario">

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Cedula">Cédula (Empleado)</label>
          <select id="Cedula" name="Cedula" required></select>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="NombreUsuario">Nombre de usuario</label>
          <input type="text" id="NombreUsuario" name="Nombre_de_Usuario" required maxlength="60">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Correo">Correo (opcional)</label>
          <input type="email" id="Correo" name="Correo" maxlength="100">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Contrasena">Contraseña <small id="helpPass" style="color:#777;"></small></label>
          <div style="position:relative;">
            <input type="password" id="Contrasena" name="Contrasena" maxlength="255" style="padding-right:36px;">
            <button type="button" id="togglePass" style="position:absolute;right:6px;top:6px;background:transparent;border:0;cursor:pointer;">👁️</button>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="IdRol">Rol</label>
          <select id="IdRol" name="IdRol" required></select>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarUsuario">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarUsuario">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirm Eliminar -->
<div class="modal" id="modalConfirmUsuario" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header">
      <h3>Confirmar eliminación</h3>
    </div>
    <p>¿Está seguro de eliminar este usuario?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoUsuario">No</button>
      <button type="button" class="btn-eliminar" id="btnSiUsuario">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toastUsuario" class="toast" style="display:none;"></div>

<script src="js/usuarios.js?v=<?php echo time(); ?>" defer></script>

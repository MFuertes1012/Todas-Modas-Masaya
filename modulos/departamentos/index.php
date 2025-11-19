<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Departamentos</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoDepartamento" class="btn-nuevo">Nuevo departamento</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarDepartamento" class="input-buscar"
             placeholder="Buscar por nombre">
    </div>

    <table class="tabla-vistas" id="tablaDepartamentos">
      <thead>
        <tr>
          <th style="width:110px;">ID</th>
          <th>Nombre</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear y Editar -->
<div class="modal" id="modalDepartamento" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloDepto">Información del departamento</h3>
    </div>

    <form id="formDepartamento" autocomplete="off">
      <input type="hidden" name="IdDepartamento" id="IdDepartamento">

      <div class="form-row">
        <div  class="icon-slot" > <img src="img/departamento.svg" alt=""> </div>
        <div class="form-field">
          <label for="Nombre">Nombre</label>
          <input type="text" name="Nombre" id="Nombre" required>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarDepto">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarDepto">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!--  eliminar -->
<div class="modal" id="modalConfirmDepto" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar este departamento?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoDepto">No</button>
      <button type="button" class="btn-eliminar" id="btnSiDepto">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>

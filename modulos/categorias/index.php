<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Categorías</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevaCategoria" class="btn-nuevo">Nueva categoría</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarCategoria" class="input-buscar"
             placeholder="Buscar por descripción">
    </div>

    <table class="tabla-vistas" id="tablaCategorias">
      <thead>
        <tr>
          <th style="width:110px;">ID</th>
          <th>Descripción</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Crear Y Editar -->
<div class="modal" id="modalCategoria" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloCat">Información de la categoría</h3>
    </div>

    <form id="formCategoria" autocomplete="off">
      <input type="hidden" name="IdCategoria" id="IdCategoria">
      <div class="form-row">
         <div  class="icon-slot" > <img src="img/categorias.svg" alt=""> </div>
        <div class="form-field">
          <label for="Descripcion">Descripción</label>
          <input type="text" name="Descripcion" id="Descripcion" required>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarCat">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarCat">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirmación al eliminar -->
<div class="modal" id="modalConfirmCat" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar esta categoría?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoCat">No</button>
      <button type="button" class="btn-eliminar" id="btnSiCat">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>

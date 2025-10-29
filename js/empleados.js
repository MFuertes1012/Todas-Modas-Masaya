// js/empleados.js
(() => {
  const $  = (s) => document.querySelector(s);

  // --------- Elementos principales ----------
  const modal        = $('#modalEmpleado');
  const form         = $('#formEmpleado');
  const modalTitulo  = $('#modalTitulo');
  const btnNuevo     = $('#btnNuevoEmpleado');
  const btnCancelar  = $('#btnCancelar');

  const modalConfirm = $('#modalConfirm');
  const btnNo        = $('#btnNo');
  const btnSi        = $('#btnSi');

  const toast        = $('#toast');
  const inputBuscar  = $('#buscarEmpleado');

  let modo = 'crear';               // 'crear' | 'editar'
  let cedulaAEliminar = null;

  // --------- Utilitarios ----------
  function showToast(msg, type='success'){
    if (!toast) return;
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success' ? 'success':'error');
    toast.style.display = 'block';
    setTimeout(()=> (toast.style.display = 'none'), 2200);
  }

  function abrirModalCrear(){
    modo = 'crear';
    modalTitulo.textContent = 'Nuevo empleado';
    form.reset();
    $('#originalCedula').value = '';
    modal.setAttribute('aria-hidden','false');
  }

  function abrirModalEditar(emp){
    modo = 'editar';
    modalTitulo.textContent = 'Editar empleado';
    form.reset();

    $('#originalCedula').value = emp.Cedula;
    $('#Cedula').value        = emp.Cedula;
    $('#Nombre').value        = emp.Nombre;
    $('#Apellido').value      = emp.Apellido;
    $('#Direccion').value     = emp.Direccion || '';
    $('#Telefono').value      = emp.Telefono  || '';
    $('#IdCargo').value       = emp.IdCargo;

    modal.setAttribute('aria-hidden','false');
  }

  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }

  function abrirConfirm(cedula){
    cedulaAEliminar = cedula;
    modalConfirm.setAttribute('aria-hidden','false');
  }
  function cerrarConfirm(){
    cedulaAEliminar = null;
    modalConfirm.setAttribute('aria-hidden','true');
  }

  // --------- Tabla ----------
  async function cargarTabla(){
    const tbody = document.querySelector('#tablaEmpleados tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8">Cargando...</td></tr>`;

    try {
      const r = await fetch('modulos/empleados/api_listar.php', { credentials:'same-origin' });
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="8">Sin datos</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      for (const e of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${e.Cedula}</td>
          <td>${e.Nombre}</td>
          <td>${e.Apellido}</td>
          <td>${e.Direccion ?? ''}</td>
          <td>${e.Telefono ?? ''}</td>
          <td>${e.Cargo ?? ''}</td>
          <td style="text-align:center;">
            <button class="btn-icon btn-edit" title="Editar">
              <img src="img/editar.png" alt="Editar">
            </button>
          </td>
          <td style="text-align:center;">
            <button class="btn-icon btn-del" title="Eliminar">
              <img src="img/eliminar.png" alt="Eliminar">
            </button>
          </td>
        `;
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirModalEditar(e));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(e.Cedula));
        tbody.appendChild(tr);
      }

      // Reinicia filtro después de recarga
      if (inputBuscar && inputBuscar.value.trim() !== '') {
        aplicarFiltro(inputBuscar.value.trim().toLowerCase());
      }
    } catch (err){
      tbody.innerHTML = `<tr><td colspan="8">Error cargando datos</td></tr>`;
      console.error(err);
    }
  }

  // --------- Guardar (crear/editar) ----------
  async function guardarEmpleado(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/empleados/api_crear.php'
      : 'modulos/empleados/api_actualizar.php';

    try {
      const r   = await fetch(url, { method:'POST', body: fd, credentials:'same-origin' });
      const res = await r.json();
      if (res.ok){
        cerrarModal();
        await cargarTabla();
        showToast(modo === 'crear' ? 'Se agregó con éxito' : 'Se editó con éxito', 'success');
      } else {
        showToast(res.msg || 'Error al guardar', 'error');
      }
    } catch (err){
      console.error(err);
      showToast('Error de red', 'error');
    }
  }

  // --------- Eliminar ----------
  async function eliminarEmpleado(){
    if (!cedulaAEliminar){ return cerrarConfirm(); }
    try {
      const fd = new FormData();
      fd.append('Cedula', cedulaAEliminar);

      const r   = await fetch('modulos/empleados/api_eliminar.php', { method:'POST', body: fd, credentials:'same-origin' });
      const res = await r.json();

      cerrarConfirm();

      if (res.ok){
        await cargarTabla();
        showToast('Se eliminó con éxito', 'success');
      } else {
        showToast(res.msg || 'No se pudo eliminar', 'error');
      }
    } catch (err){
      console.error(err);
      showToast('Error de red', 'error');
    }
  }

  // --------- Búsqueda (en vivo) ----------
  function aplicarFiltro(q){
    q = (q || '').toLowerCase();
    const filas = document.querySelectorAll('#tablaEmpleados tbody tr');
    filas.forEach(tr => {
      // cédula, nombre, apellido (primeras 3 columnas)
      const celdas = Array.from(tr.children).slice(0, 3);
      const hit = celdas.some(td => (td.textContent || '').toLowerCase().includes(q));
      tr.style.display = hit ? '' : 'none';
    });
  }

  // --------- Eventos ----------
  btnNuevo   && btnNuevo.addEventListener('click', abrirModalCrear);
  btnCancelar&& btnCancelar.addEventListener('click', cerrarModal);
  form       && form.addEventListener('submit', guardarEmpleado);

  btnNo      && btnNo.addEventListener('click', cerrarConfirm);
  btnSi      && btnSi.addEventListener('click', eliminarEmpleado);

  inputBuscar&& inputBuscar.addEventListener('input', () => aplicarFiltro(inputBuscar.value));

  // Primera carga
  cargarTabla();
})();

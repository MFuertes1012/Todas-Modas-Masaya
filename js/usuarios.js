(() => {
  const $ = s => document.querySelector(s);

  const tbody       = document.querySelector('#tablaUsuarios tbody');
  const btnNuevo    = $('#btnNuevoUsuario');
  const inputBuscar = $('#buscarUsuario');

  const modal       = $('#modalUsuario');
  const tituloModal = $('#modalTituloUsuario');
  const form        = $('#formUsuario');
  const idInp       = $('#IdUsuario');
  const cedulaSel   = $('#Cedula');
  const userInp     = $('#NombreUsuario');
  const correoInp   = $('#Correo');
  const passInp     = $('#Contrasena');
  const rolSel      = $('#IdRol');
  const btnCancelar = $('#btnCancelarUsuario');
  const helpPass    = $('#helpPass');

  const btnEye      = $('#togglePass');

  const modalConf   = $('#modalConfirmUsuario');
  const btnNo       = $('#btnNoUsuario');
  const btnSi       = $('#btnSiUsuario');

  const toast       = $('#toastUsuario');

  let modo = 'crear';
  let idEliminar = null;

  function toastMsg(msg, type='success'){
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success'?'success':'error');
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 2200);
  }

  // Combos
  async function cargarRoles(){
    const r = await fetch('modulos/usuarios/api_combo_roles.php', {credentials:'same-origin'});
    const data = await r.json();
    rolSel.innerHTML = '<option value="">Seleccione rol</option>';
    data.forEach(x => {
      const op = document.createElement('option');
      op.value = x.IdRol; op.textContent = x.Descripcion;
      rolSel.appendChild(op);
    });
  }
  async function cargarEmpleados(){
    const r = await fetch('modulos/usuarios/api_combo_empleados.php', {credentials:'same-origin'});
    const data = await r.json();
    cedulaSel.innerHTML = '<option value="">Seleccione cédula</option>';
    data.forEach(x => {
      const op = document.createElement('option');
      op.value = x.Cedula; op.textContent = `${x.Cedula} — ${x.NombreCompleto}`;
      cedulaSel.appendChild(op);
    });
  }

  // UI
  function abrirCrear(){
    modo = 'crear';
    tituloModal.textContent = 'Nuevo usuario';
    form.reset();
    idInp.value = '';
    helpPass.textContent = '(obligatoria)';
    modal.setAttribute('aria-hidden','false');
    cedulaSel.focus();
  }
  function abrirEditar(row){
    modo = 'editar';
    tituloModal.textContent = 'Editar usuario';
    form.reset();
    idInp.value = row.IdUsuario;
    cedulaSel.value = row.Cedula;
    userInp.value = row.Nombre_de_Usuario;
    correoInp.value = row.Correo || '';
    rolSel.value = row.IdRol || '';
    passInp.value = '';
    helpPass.textContent = '(déjala vacía para no cambiarla)';
    modal.setAttribute('aria-hidden','false');
    userInp.focus();
  }
  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }

  function abrirConfirm(id){
    idEliminar = id;
    modalConf.setAttribute('aria-hidden','false');
  }
  function cerrarConfirm(){
    idEliminar = null;
    modalConf.setAttribute('aria-hidden','true');
  }

  // Tabla
  async function cargarTabla(){
    tbody.innerHTML = `<tr><td colspan="7">Cargando...</td></tr>`;
    try{
      const r = await fetch('modulos/usuarios/api_listar.php', {credentials:'same-origin'});
      const data = await r.json();
      if(!Array.isArray(data) || data.length===0){
        tbody.innerHTML = `<tr><td colspan="7">Sin datos</td></tr>`;
        return;
      }
      tbody.innerHTML = '';
      for(const u of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${u.IdUsuario}</td>
          <td>${u.Cedula}</td>
          <td>${u.Nombre_de_Usuario}</td>
          <td>${u.Correo ?? ''}</td>
          <td>${u.Rol}</td>
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
        tr.querySelector('.btn-edit').addEventListener('click', async () => {
          // necesitamos IdRol para setear el combo al editar; lo pedimos de nuevo con join
          // (alternativamente podrías añadir IdRol en api_listar.php)
          const detalle = {...u};
          // Si no viene IdRol, opcional: podrías agregar en api_listar SELECT u.IdRol AS IdRol
          // y aquí usar detalle.IdRol = u.IdRol;
          // Para mantenerlo sencillo, ajusta api_listar y añade u.IdRol.
          abrirEditar(detalle);
        });
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(u.IdUsuario));
        tbody.appendChild(tr);
      }
      if(inputBuscar?.value.trim()) aplicarFiltro(inputBuscar.value.trim());
    }catch(err){
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="7">Error cargando datos</td></tr>`;
    }
  }

  // Guardar / Eliminar
  async function guardar(e){
    e.preventDefault();

    if(modo==='crear' && !passInp.value){
      toastMsg('La contraseña es obligatoria', 'error'); return;
    }

    const fd = new FormData(form);
    const url = (modo==='crear')
      ? 'modulos/usuarios/api_crear.php'
      : 'modulos/usuarios/api_actualizar.php';

    try{
      const r = await fetch(url, {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      if(res.ok){
        cerrarModal();
        await cargarTabla();
        toastMsg(modo==='crear' ? 'Se agregó con éxito' : 'Se editó con éxito', 'success');
      }else{
        toastMsg(res.msg || 'Error', 'error');
      }
    }catch(err){
      console.error(err);
      toastMsg('Error de red', 'error');
    }
  }

  async function eliminarUsuario(){
    if(!idEliminar) return cerrarConfirm();
    const fd = new FormData();
    fd.append('IdUsuario', idEliminar);
    try{
      const r = await fetch('modulos/usuarios/api_eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      cerrarConfirm();
      if(res.ok){
        await cargarTabla();
        toastMsg('Se eliminó con éxito', 'success');
      }else{
        toastMsg(res.msg || 'No se pudo eliminar', 'error');
      }
    }catch(err){
      console.error(err);
      toastMsg('Error de red', 'error');
    }
  }

  function aplicarFiltro(q){
    q = q.toLowerCase();
    document.querySelectorAll('#tablaUsuarios tbody tr').forEach(tr=>{
      const t = Array.from(tr.children).slice(0,5).map(td => (td.textContent||'').toLowerCase()).join(' ');
      tr.style.display = t.includes(q) ? '' : 'none';
    });
  }

  // Eye toggle
  btnEye?.addEventListener('click', () => {
    passInp.type = passInp.type === 'password' ? 'text' : 'password';
  });

  // Eventos
  btnNuevo?.addEventListener('click', abrirCrear);
  btnCancelar?.addEventListener('click', cerrarModal);
  form?.addEventListener('submit', guardar);
  btnNo?.addEventListener('click', cerrarConfirm);
  btnSi?.addEventListener('click', eliminarUsuario);
  inputBuscar?.addEventListener('input', () => aplicarFiltro(inputBuscar.value));

  // Init combos + tabla
  Promise.all([cargarRoles(), cargarEmpleados()]).then(cargarTabla);
})();

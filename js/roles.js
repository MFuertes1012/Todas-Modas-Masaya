(() => {
  const $ = s => document.querySelector(s);

  const tbody       = document.querySelector('#tablaRoles tbody');
  const btnNuevo    = $('#btnNuevoRol');
  const inputBuscar = $('#buscarRol');

  const modal       = $('#modalRol');
  const tituloModal = $('#modalTituloRol');
  const form        = $('#formRol');
  const idRolInp    = $('#IdRol');
  const descInp     = $('#DescripcionRol');
  const btnCancelar = $('#btnCancelarRol');

  const modalConf   = $('#modalConfirmRol');
  const btnNo       = $('#btnNoRol');
  const btnSi       = $('#btnSiRol');

  const toast       = $('#toastRol');

  let modo = 'crear';
  let idEliminar = null;

  function toastMsg(msg, type='success'){
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success'?'success':'error');
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 2200);
  }

  function abrirCrear(){
    modo = 'crear';
    tituloModal.textContent = 'Nuevo rol';
    idRolInp.value = '';
    descInp.value = '';
    modal.setAttribute('aria-hidden','false');
    descInp.focus();
  }
  function abrirEditar(row){
    modo = 'editar';
    tituloModal.textContent = 'Editar rol';
    idRolInp.value = row.IdRol;
    descInp.value = row.Descripcion;
    modal.setAttribute('aria-hidden','false');
    descInp.focus();
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

  async function cargarTabla(){
    tbody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;
    try{
      const r = await fetch('modulos/roles/api_listar.php', {credentials:'same-origin'});
      const data = await r.json();
      if(!Array.isArray(data) || data.length===0){
        tbody.innerHTML = `<tr><td colspan="4">Sin datos</td></tr>`;
        return;
      }
      tbody.innerHTML = '';
      for(const rol of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${rol.IdRol}</td>
          <td>${rol.Descripcion}</td>
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
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(rol));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(rol.IdRol));
        tbody.appendChild(tr);
      }
      if(inputBuscar?.value.trim()) aplicarFiltro(inputBuscar.value.trim());
    }catch(err){
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="4">Error cargando datos</td></tr>`;
    }
  }

  async function guardarRol(e){
    e.preventDefault();
    const fd = new FormData(form);
    const url = (modo==='crear')
      ? 'modulos/roles/api_crear.php'
      : 'modulos/roles/api_actualizar.php';
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

  async function eliminarRol(){
    if(!idEliminar) return cerrarConfirm();
    const fd = new FormData();
    fd.append('IdRol', idEliminar);
    try{
      const r = await fetch('modulos/roles/api_eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
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
    document.querySelectorAll('#tablaRoles tbody tr').forEach(tr=>{
      const id  = (tr.children[0]?.textContent || '').toLowerCase();
      const des = (tr.children[1]?.textContent || '').toLowerCase();
      tr.style.display = (id.includes(q) || des.includes(q)) ? '' : 'none';
    });
  }

  // Eventos
  btnNuevo?.addEventListener('click', abrirCrear);
  btnCancelar?.addEventListener('click', cerrarModal);
  form?.addEventListener('submit', guardarRol);
  btnNo?.addEventListener('click', cerrarConfirm);
  btnSi?.addEventListener('click', eliminarRol);
  inputBuscar?.addEventListener('input', () => aplicarFiltro(inputBuscar.value));

  // Init
  cargarTabla();
})();

(() => {
  console.log('🟣 cargos.js ejecutado');
  const $ = s => document.querySelector(s);

  // Elementos
  const tbody        = document.querySelector('#tablaCargos tbody');
  const btnNuevo     = $('#btnNuevoCargo');
  const inputBuscar  = $('#buscarCargo');

  const modal        = $('#modalCargo');
  const tituloModal  = $('#modalTituloCargo');
  const form         = $('#formCargo');
  const idCargoInp   = $('#IdCargo');
  const nombreInp    = $('#NombreCargo');
  const btnCancelar  = $('#btnCancelarCargo');

  const modalConfirm = $('#modalConfirmCargo');
  const btnNo        = $('#btnNoCargo');
  const btnSi        = $('#btnSiCargo');

  const toast        = $('#toastCargo');

  let modo = 'crear';
  let idAEliminar = null;

  function showToast(msg, type='success'){
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success'?'success':'error');
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 2200);
  }

  function abrirCrear(){
    modo = 'crear';
    tituloModal.textContent = 'Nuevo cargo';
    idCargoInp.value = '';
    nombreInp.value = '';
    modal.setAttribute('aria-hidden','false');
    nombreInp.focus();
  }
  function abrirEditar(row){
    modo = 'editar';
    tituloModal.textContent = 'Editar cargo';
    idCargoInp.value = row.IdCargo;
    nombreInp.value = row.Nombre;
    modal.setAttribute('aria-hidden','false');
    nombreInp.focus();
  }
  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }

  function abrirConfirm(id){
    idAEliminar = id;
    modalConfirm.setAttribute('aria-hidden','false');
  }
  function cerrarConfirm(){
    idAEliminar = null;
    modalConfirm.setAttribute('aria-hidden','true');
  }

  async function cargarTabla(){
    tbody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;
    try{
      const r = await fetch('modulos/cargos/api_listar.php', {credentials:'same-origin'});
      const data = await r.json();
      if(!Array.isArray(data) || data.length === 0){
        tbody.innerHTML = `<tr><td colspan="4">Sin datos</td></tr>`;
        return;
      }
      tbody.innerHTML = '';
      for(const c of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${c.IdCargo}</td>
          <td>${c.Nombre}</td>
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
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(c));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(c.IdCargo));
        tbody.appendChild(tr);
      }
      // Si había texto en el buscador, reaplica filtro
      if (inputBuscar?.value.trim()){
        aplicarFiltro(inputBuscar.value.trim());
      }
    }catch(e){
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="4">Error cargando datos</td></tr>`;
    }
  }

  async function guardarCargo(e){
    e.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/cargos/api_crear.php'
      : 'modulos/cargos/api_actualizar.php';

    try{
      const r = await fetch(url, {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      if(res.ok){
        cerrarModal();
        await cargarTabla();
        showToast(modo==='crear' ? 'Se agregó con éxito' : 'Se editó con éxito', 'success');
      }else{
        showToast(res.msg || 'Error al guardar', 'error');
      }
    }catch(err){
      console.error(err);
      showToast('Error de red', 'error');
    }
  }

  async function eliminarCargo(){
    if(!idAEliminar) return cerrarConfirm();
    const fd = new FormData();
    fd.append('IdCargo', idAEliminar);
    try{
      const r = await fetch('modulos/cargos/api_eliminar.php', {
        method:'POST', body: fd, credentials:'same-origin'
      });
      const res = await r.json();
      cerrarConfirm();
      if(res.ok){
        await cargarTabla();
        showToast('Se eliminó con éxito', 'success');
      }else{
        showToast(res.msg || 'No se pudo eliminar', 'error');
      }
    }catch(err){
      console.error(err);
      showToast('Error de red', 'error');
    }
  }

  function aplicarFiltro(q){
    q = (q || '').toLowerCase();
    document.querySelectorAll('#tablaCargos tbody tr').forEach(tr=>{
      const id     = (tr.children[0]?.textContent || '').toLowerCase();
      const nombre = (tr.children[1]?.textContent || '').toLowerCase();
      tr.style.display = (id.includes(q) || nombre.includes(q)) ? '' : 'none';
    });
  }

  // Eventos
  btnNuevo?.addEventListener('click', abrirCrear);
  btnCancelar?.addEventListener('click', cerrarModal);
  form?.addEventListener('submit', guardarCargo);
  btnNo?.addEventListener('click', cerrarConfirm);
  btnSi?.addEventListener('click', eliminarCargo);
  inputBuscar?.addEventListener('input', () => aplicarFiltro(inputBuscar.value));

  // Init
  cargarTabla();
})();

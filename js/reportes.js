(() => {
  const $ = s => document.querySelector(s);

  const tipoSel        = $('#tipoReporte');
  const desdeIn        = $('#repDesde');
  const hastaIn        = $('#repHasta');
  const btnVer         = $('#btnVerReporte');
  const btnPdf         = $('#btnPdfReporte');

  const filtroCategoria = $('#filtroCategoria');
  const filtroProducto  = $('#filtroProducto');
  const filtroProveedor = $('#filtroProveedor');
  const filtroCedula    = $('#filtroCedula');
  const filtroCliente   = $('#filtroCliente');

  const tabla   = $('#tablaReporte');
  const thead   = tabla ? tabla.querySelector('thead') : null;
  const tbody   = tabla ? tabla.querySelector('tbody') : null;

  const toast   = document.getElementById('toast');

  const showMsg = (msg, type = 'error') => {
    if (toast) {
      toast.textContent = msg;
      toast.className = 'toast ' + (type === 'success' ? 'success' : 'error');
      toast.style.display = 'block';
      setTimeout(() => toast.style.display = 'none', 2200);
    } else {
      alert(msg);
    }
  };

  // Mostrar / ocultar filtros extra según el tipo de reporte
  function actualizarFiltrosVisibles() {
    if (!tipoSel) return;

    const tipo = tipoSel.value;

    // Ocultar todos
    [filtroCategoria, filtroProducto, filtroProveedor, filtroCedula, filtroCliente]
      .forEach(s => { if (s) { s.style.display = 'none'; s.value = ''; }});

    // Mostrar según tipo
    if (tipo === 'inventario') {
      if (filtroCategoria) filtroCategoria.style.display = 'block';
      if (filtroProducto)  filtroProducto.style.display  = 'block';
    } else if (tipo === 'compras') {
      if (filtroProveedor) filtroProveedor.style.display = 'block';
      if (filtroProducto)  filtroProducto.style.display  = 'block';
    } else if (tipo === 'empleados') {
      if (filtroCedula) filtroCedula.style.display = 'block';
    } else if (tipo === 'salidas') {
      if (filtroCliente) filtroCliente.style.display = 'block';
    }

    // Limpiar vista previa cuando cambiamos de tipo
    if (thead && tbody) {
      thead.innerHTML = '';
      tbody.innerHTML = '<tr><td>No hay datos para mostrar.</td></tr>';
    }
  }

  // ==== Cargar datos para vista previa ====
  async function cargarDatosReporte() {
    if (!tipoSel || !thead || !tbody) return;

    const tipo  = tipoSel.value;
    const desde = desdeIn.value;
    const hasta = hastaIn.value;

    const params = new URLSearchParams();
    params.append('tipo', tipo);
    if (desde) params.append('desde', desde);
    if (hasta) params.append('hasta', hasta);

    // Filtros extra
    if (tipo === 'inventario') {
      if (filtroCategoria?.value) params.append('categoria', filtroCategoria.value);
      if (filtroProducto?.value)  params.append('producto',  filtroProducto.value);
    } else if (tipo === 'compras') {
      if (filtroProveedor?.value) params.append('proveedor', filtroProveedor.value);
      if (filtroProducto?.value)  params.append('producto',  filtroProducto.value);
    } else if (tipo === 'empleados') {
      if (filtroCedula?.value) params.append('cedula', filtroCedula.value);
    } else if (tipo === 'salidas') {
      if (filtroCliente?.value) params.append('cliente', filtroCliente.value);
    }

    try {
      const r = await fetch('modulos/reportes/datos.php?' + params.toString(), {
        credentials: 'same-origin'
      });

      const res = await r.json();
      if (!res.ok) {
        showMsg(res.msg || 'No se pudo obtener el reporte');
        return;
      }

      const cols = res.columns || [];
      const rows = res.rows || [];

      renderTabla(cols, rows);

    } catch (e) {
      console.error(e);
      showMsg('Error de red al obtener datos');
    }
  }

  // Renderizar tabla en la vista previa
  function renderTabla(columns, rows) {
    thead.innerHTML = '';
    tbody.innerHTML = '';

    if (!columns.length) {
      tbody.innerHTML = '<tr><td>No hay datos para mostrar.</td></tr>';
      return;
    }

    const trHead = document.createElement('tr');
    columns.forEach(col => {
      const th = document.createElement('th');
      th.textContent = col;
      trHead.appendChild(th);
    });
    thead.appendChild(trHead);

    if (!rows.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = columns.length;
      td.textContent = 'No hay datos para el filtro seleccionado.';
      tr.appendChild(td);
      tbody.appendChild(tr);
      return;
    }

    rows.forEach(row => {
      const tr = document.createElement('tr');
      row.forEach(cell => {
        const td = document.createElement('td');
        td.textContent = cell;
        tr.appendChild(td);
      });
      tbody.appendChild(tr);
    });
  }

  // DESCARGAR PDF
  function descargarPdfReporte() {
    if (!tipoSel) return;

    const tipo  = tipoSel.value;
    const desde = desdeIn.value;
    const hasta = hastaIn.value;

    const params = new URLSearchParams();
    params.append('tipo', tipo);
    if (desde) params.append('desde', desde);
    if (hasta) params.append('hasta', hasta);

    if (tipo === 'inventario') {
      if (filtroCategoria?.value) params.append('categoria', filtroCategoria.value);
      if (filtroProducto?.value)  params.append('producto',  filtroProducto.value);
    } else if (tipo === 'compras') {
      if (filtroProveedor?.value) params.append('proveedor', filtroProveedor.value);
      if (filtroProducto?.value)  params.append('producto',  filtroProducto.value);
    } else if (tipo === 'empleados') {
      if (filtroCedula?.value) params.append('cedula', filtroCedula.value);
    } else if (tipo === 'salidas') {
      if (filtroCliente?.value) params.append('cliente', filtroCliente.value);
    }

    window.open('modulos/reportes/pdf.php?' + params.toString(), '_blank');
  }

  tipoSel?.addEventListener('change', actualizarFiltrosVisibles);

  btnVer?.addEventListener('click', e => {
    e.preventDefault();
    cargarDatosReporte();
  });

  btnPdf?.addEventListener('click', e => {
    e.preventDefault();
    descargarPdfReporte();
  });

  actualizarFiltrosVisibles();
})();

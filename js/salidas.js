(() => {
  const $ = s => document.querySelector(s);

  const tbody      = $('#tablaSalidas tbody');
  const qInput     = $('#buscarSalida');
  const dInput     = $('#ventaDesde');
  const hInput     = $('#ventaHasta');
  const toast      = $('#toast');

  const modal      = $('#modalSalida');
  const titulo     = $('#modalTituloSalida');
  const form       = $('#formSalida');
  const btnNueva   = $('#btnNuevaSalida');
  const btnCancel  = $('#btnCancelarSalida');
  const btnAdd     = $('#btnAgregarFila');
  const bodyDet    = $('#detalleBody');
  const tpl        = $('#filaDetalleVentaTemplate');
  const totalOut   = $('#TotalVenta');

  const factModal  = $('#modalFacturaVenta');
  const factId     = $('#factVId');
  const factVend   = $('#factVendedor');
  const factCli    = $('#factCliente');
  const factMetodo = $('#factMetodo');
  const factFecha  = $('#factVFecha');
  const factBody   = $('#factVentaBody');
  const factTotal  = $('#factVTotal');
  const factClose  = $('#btnCerrarFacturaVenta');
  const factLink   = $('#btnDescargarFacturaVenta');

  let salidas = [];
  let modo = 'crear';

  const toastShow = (m, t = 'success') => {
    if (!toast) return;
    toast.textContent = m;
    toast.className = 'toast ' + (t === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 2200);
  };

  //  Cargar lista 
  async function cargar() {
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="8">Cargando...</td></tr>`;
    try {
      const params = new URLSearchParams();
      if (qInput?.value) params.append('q', qInput.value.trim());
      if (dInput?.value) params.append('desde', dInput.value);
      if (hInput?.value) params.append('hasta', hInput.value);

      const r = await fetch('modulos/salidas/listar.php?' + params.toString(), {
        credentials: 'same-origin'
      });
      const data = await r.json();

      if (!data.ok) {
        tbody.innerHTML = `<tr><td colspan="8">Error: ${data.msg}</td></tr>`;
        return;
      }

      salidas = data.data || [];
      render();
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="8">Error al cargar</td></tr>`;
    }
  }

  function render() {
    if (!tbody) return;

    const q = (qInput?.value || '').toLowerCase();
    const d = dInput?.value || '';
    const h = hInput?.value || '';

    tbody.innerHTML = '';
    let count = 0;

    for (const s of salidas) {
      const text = (s.Cliente || '').toLowerCase();
      if (q && !text.includes(q)) continue;

      const f = (s.Fecha || '').slice(0, 10);
      if (d && f < d) continue;
      if (h && f > h) continue;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${s.IdVenta}</td>
        <td>${s.Vendedor || ''}</td>
        <td>${s.Cliente || ''}</td>
        <td>${s.Metodo_de_pago || ''}</td>
        <td>${s.Fecha || ''}</td>
        <td style="text-align:center;"><button class="btn-icon btn-fact"><img src="img/factura.png"></button></td>
        <td style="text-align:center;"><button class="btn-icon btn-edit"><img src="img/editar.png"></button></td>
        <td style="text-align:center;"><button class="btn-icon btn-del"><img src="img/eliminar.png"></button></td>
      `;

      tr.querySelector('.btn-fact').onclick = () => verFactura(s.IdVenta);
      tr.querySelector('.btn-edit').onclick = () => editar(s.IdVenta);
      tr.querySelector('.btn-del').onclick  = () => eliminar(s.IdVenta);

      tbody.appendChild(tr);
      count++;
    }

    if (!count) tbody.innerHTML = `<tr><td colspan="8">No hay resultados</td></tr>`;
  }

  // Modal Nueva / Editar 
  function abrir() {
    modo = 'crear';
    titulo.textContent = 'Nueva salida';
    form.reset();
    const hid = $('#IdVenta');
    if (hid) hid.value = '';
    bodyDet.innerHTML = '';
    agregarFila();
    recalcularTotal();
    modal.setAttribute('aria-hidden', 'false');
  }

  function cerrar() {
    modal.setAttribute('aria-hidden', 'true');
  }

  function agregarFila(det = null) {
    if (!tpl || !bodyDet) return;

    const frag = tpl.content.cloneNode(true);
    const tr   = frag.querySelector('tr');

    const sel  = tr.querySelector('.sel-producto');
    const mInp = tr.querySelector('.inp-marca');
    const tInp = tr.querySelector('.inp-talla');
    const cInp = tr.querySelector('.inp-cantidad');
    const pInp = tr.querySelector('.inp-precio');
    const rem  = tr.querySelector('.btn-remove-row');

    
    if (det) {
      sel.value   = det.IdProducto;
      mInp.value  = det.Marca || '';
      tInp.value  = det.Talla || '';
      cInp.value  = det.Cantidad || 1;
      pInp.value  = Number(det.PrecioUnitario || 0).toFixed(2);
    }

    const fillFromSel = () => {
      const opt = sel.selectedOptions[0];

      if (!opt || !opt.value) {
        mInp.value = '';
        tInp.value = '';
        pInp.value = '0';
        cInp.value = 1;
        recalcularFila(tr);
        recalcularTotal();
        return;
      }

      // Llenar datos del producto
      mInp.value = opt.dataset.marca || '';
      tInp.value = opt.dataset.talla || '';
      pInp.value = opt.dataset.precio || '0';

      // Validar contra stock
      const max = parseInt(opt.dataset.stock || '0', 10);
      let val   = parseInt(cInp.value || '0', 10);

      if (isNaN(val) || val < 1) val = 1;

      if (!isNaN(max) && val > max) {
        val = max;
        if (max > 0) toastShow(`Solo hay ${max} en stock`, 'error');
      }

      cInp.value = val;
      recalcularFila(tr);
      recalcularTotal();
    };

    sel.addEventListener('change', fillFromSel);
    cInp.addEventListener('input', fillFromSel);
    rem.addEventListener('click', () => {
      tr.remove();
      recalcularTotal();
    });

    bodyDet.appendChild(tr);

    // Disparar change al crear la fila para rellenar si ya hay producto seleccionado
    sel.dispatchEvent(new Event('change'));

    recalcularFila(tr);
    recalcularTotal();
  }

  // Cálculos
  const recalcularFila = (tr) => {
    const cant = parseFloat(tr.querySelector('.inp-cantidad')?.value || '0');
    const prec = parseFloat(tr.querySelector('.inp-precio')?.value || '0');
    const celda = tr.querySelector('.celda-subtotal');
    if (celda) celda.textContent = `C$ ${(cant * prec).toFixed(2)}`;
  };

  const recalcularTotal = () => {
    let total = 0;
    bodyDet?.querySelectorAll('tr').forEach(tr => {
      const c = parseFloat(tr.querySelector('.inp-cantidad')?.value || '0');
      const p = parseFloat(tr.querySelector('.inp-precio')?.value || '0');
      total += c * p;
    });
    if (totalOut) totalOut.value = `C$ ${total.toFixed(2)}`;
  };

  // Guardar
  async function guardar(ev) {
    ev.preventDefault();
    if (!bodyDet.querySelector('tr')) {
      toastShow('Agregue al menos un producto', 'error');
      return;
    }

    const url = (modo === 'crear')
      ? 'modulos/salidas/crear.php'
      : 'modulos/salidas/actualizar.php';

    const fd = new FormData(form);

    try {
      const r = await fetch(url, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const res = await r.json();

      if (res.ok) {
        cerrar();
        await cargar();
        toastShow(res.msg || 'Guardado correctamente');
      } else {
        toastShow(res.msg || 'No se pudo guardar', 'error');
      }
    } catch (e) {
      console.error(e);
      toastShow('Error de red', 'error');
    }
  }

  //  Editar 
  async function editar(IdVenta) {
    try {
      const r = await fetch(
        'modulos/salidas/detalle.php?IdVenta=' + encodeURIComponent(IdVenta),
        { credentials: 'same-origin' }
      );
      const res = await r.json();

      if (!res.ok) {
        toastShow(res.msg || 'No se pudo cargar la salida', 'error');
        return;
      }

      const cab = res.cabecera;

      modo = 'editar';
      titulo.textContent = 'Editar salida';
      form.reset();

      // Id de la salida
      const idVentaInput = $('#IdVenta');
      if (idVentaInput) idVentaInput.value = cab.IdVenta;

      // Cliente 
      const selCliente = $('#IdCliente');
      if (selCliente && cab.IdCliente) {
        selCliente.value = cab.IdCliente;
      }

      // Fecha
      const fechaInput = $('#FechaVenta');
      if (fechaInput && cab.Fecha) {
        fechaInput.value = cab.Fecha;
      }

      // Método de pago
      const metodoInput = $('#MetodoPago');
      if (metodoInput && cab.Metodo) {
        metodoInput.value = cab.Metodo;
      }

      // Detalle
      bodyDet.innerHTML = '';
      (res.detalles || []).forEach(d => agregarFila(d));
      recalcularTotal();

      modal.setAttribute('aria-hidden', 'false');
    } catch (e) {
      console.error(e);
      toastShow('Error al cargar la salida', 'error');
    }
  }

  //  Eliminar 
  function eliminar(IdVenta) {
    const m = document.createElement('div');
    m.className = 'modal';
    m.setAttribute('aria-hidden', 'false');

    m.innerHTML = `
      <div class="modal-content small">
        <div class="modal-header"><h3>Confirmar eliminación</h3></div>
        <p>¿Está seguro de eliminar la salida?</p>
        <div class="modal-actions">
          <button class="btn-cancelar">No</button>
          <button class="btn-eliminar">Sí, eliminar</button>
        </div>
      </div>
    `;

    document.body.appendChild(m);

    m.querySelector('.btn-cancelar').onclick = () => m.remove();
    m.querySelector('.btn-eliminar').onclick = async () => {
      try {
        const body = new URLSearchParams();
        body.append('IdVenta', IdVenta);

        const r = await fetch('modulos/salidas/eliminar.php', {
          method: 'POST',
          body,
          credentials: 'same-origin'
        });

        const res = await r.json();
        m.remove();

        if (res.ok) {
          await cargar();
          toastShow('Salida eliminada');
        } else {
          toastShow(res.msg || 'No se pudo eliminar', 'error');
        }
      } catch (e) {
        console.error(e);
        m.remove();
        toastShow('Error de red', 'error');
      }
    };
  }

  // Ver factura
  async function verFactura(IdVenta) {
    try {
      const r = await fetch(
        'modulos/salidas/detalle.php?IdVenta=' + encodeURIComponent(IdVenta),
        { credentials: 'same-origin' }
      );

      const res = await r.json();
      if (!res.ok) {
        toastShow(res.msg || 'No se pudo cargar', 'error');
        return;
      }

      const cab = res.cabecera;

      factId.textContent     = cab.IdVenta;
      factVend.textContent   = cab.Vendedor;
      factCli.textContent    = cab.Cliente;
      factMetodo.textContent = cab.Metodo;
      factFecha.textContent  = cab.Fecha;

      factBody.innerHTML = '';

      (res.detalles || []).forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.Producto || ''}</td>
          <td>${d.Marca || ''}</td>
          <td>${d.Talla || ''}</td>
          <td>${d.Cantidad || 0}</td>
          <td>C$ ${Number(d.PrecioUnitario).toFixed(2)}</td>
          <td>C$ ${Number(d.Subtotal).toFixed(2)}</td>
        `;
        factBody.appendChild(tr);
      });

      factTotal.textContent =
        `C$ ${Number(res.total || 0).toFixed(2)}`;

      factLink.href =
        'modulos/salidas/factura_pdf.php?IdVenta=' + encodeURIComponent(IdVenta);

      factModal.setAttribute('aria-hidden', 'false');

    } catch (e) {
      console.error(e);
      toastShow('Error de red', 'error');
    }
  }

  const cerrarFactura = () =>
    factModal.setAttribute('aria-hidden', 'true');


  btnNueva?.addEventListener('click', abrir);
  btnCancel?.addEventListener('click', cerrar);
  btnAdd?.addEventListener('click', () => agregarFila());
  form?.addEventListener('submit', guardar);

  qInput?.addEventListener('input', render);
  dInput?.addEventListener('change', render);
  hInput?.addEventListener('change', render);
  factClose?.addEventListener('click', cerrarFactura);

  cargar();
})();
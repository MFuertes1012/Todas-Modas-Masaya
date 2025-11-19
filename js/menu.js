function mostrarContenido(seccion){
  const cont = document.getElementById('contenido');
  cont.innerHTML = `<h2>${seccion}</h2>`;

  document.querySelectorAll('.item.nivel1').forEach(li => li.classList.remove('activo'));
  const match = Array.from(document.querySelectorAll('.item.nivel1'))
    .find(li => li.textContent.trim().startsWith(seccion));
  if (match) match.classList.add('activo');
}

document.addEventListener('DOMContentLoaded', () => {
  // Marcar Inicio al cargar
  mostrarContenido('Inicio');

  // Submenús desplegables
  document.querySelectorAll('.submenu').forEach(s => {
    const header = s.querySelector('.submenu-header');
    const list   = s.querySelector('.submenu-list');
    const toggle = s.querySelector('.toggle-icon');

    header.addEventListener('click', (e) => {
      e.stopPropagation();

      // Cierra otros submenús 
      document.querySelectorAll('.submenu.open').forEach(o=>{
        if(o!==s){ 
          o.classList.remove('open'); 
          const t = o.querySelector('.toggle-icon');
          if (t) t.src = 'img/mas.svg';
        }
      });

      const open = s.classList.toggle('open');
      if (toggle) toggle.src = open ? 'img/menos.svg' : 'img/mas.svg';

     
    });
  });
});

function verPerfil() {
  // Solo mostrar una pantalla de verificación, AUN EN PROCESO
  mostrarContenido('Información');
}

function cerrarSesion() {
  window.location.href = 'cerrar sesion.php';
}


//----------------------------CARGA DE MODULOS 
function cargarModuloEmpleados() {

  fetch('modulos/empleados/index.php', { credentials: 'same-origin' })
    .then(r => r.text())
    .then(html => {
      const cont = document.getElementById('contenido');
      cont.innerHTML = html;

      document.querySelectorAll('.item.nivel1').forEach(li => li.classList.remove('activo'));
      const s = document.createElement('script');
      s.src = 'js/empleados.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
      // Carga CSS del módulo si no existe
      if (!document.querySelector('link[href^="css/vistas.css"]')) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }
    });
}

function cargarModuloProveedores() {
  fetch('modulos/proveedores/index.php', { credentials: 'same-origin' })
    .then(r => r.text())
    .then(html => {
      const cont = document.getElementById('contenido');
      cont.innerHTML = html;

      if (!document.querySelector('link[href^="css/vistas.css"]')) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

      const s = document.createElement('script');
      s.src = 'js/proveedores.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
    });
}

function cargarModuloRoles() {
  const cont = document.getElementById('contenido');

  fetch('modulos/roles/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Roles');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      const prev = document.getElementById('mod-roles-js');
      if (prev) prev.remove();

      const s = document.createElement('script');
      s.id = 'mod-roles-js';
      s.src = 'js/roles.js?v=' + Date.now();
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Roles');
    });
}


function cargarModuloUsuarios() {
  const cont = document.getElementById('contenido');
  fetch('modulos/usuarios/index.php', { credentials: 'same-origin' })
    .then(r => r.text())
    .then(html => {
      cont.innerHTML = html;
      const prev = document.getElementById('mod-usuarios-js');
      if (prev) prev.remove();
      const s = document.createElement('script');
      s.id = 'mod-usuarios-js';
      s.src = 'js/usuarios.js?v=' + Date.now();
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Usuarios');
    });
}


function cargarModuloProveedores() {
  const cont = document.getElementById('contenido');

  fetch('modulos/proveedores/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Proveedores');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;


      if (!document.querySelector('link[href*="vistas.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(link);
      }

      const prev = document.getElementById('mod-proveedores-js');
      if (prev) prev.remove();

      const s = document.createElement('script');
      s.id = 'mod-proveedores-js';
      s.src = 'js/proveedores.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Proveedores');
    });
}

function cargarModuloCategorias() {
  const cont = document.getElementById('contenido');

  fetch('modulos/categorias/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Categorías');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

      // Inyectar el JS del módulo
      const prev = document.getElementById('mod-categorias-js');
      if (prev) prev.remove();
      const s = document.createElement('script');
      s.id = 'mod-categorias-js';
      s.src = 'js/categorias.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Categorías');
    });
}

function cargarModuloDepartamentos() {
  const cont = document.getElementById('contenido');

  fetch('modulos/departamentos/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Departamentos');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

      const prev = document.getElementById('mod-departamentos-js');
      if (prev) prev.remove();

      const s  = document.createElement('script');
      s.id     = 'mod-departamentos-js';
      s.src    = 'js/departamentos.js?v=' + Date.now();
      s.defer  = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Departamentos');
    });
}

function cargarModuloCargos() {
  const cont = document.getElementById('contenido');

  fetch('modulos/cargos/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Cargos');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

    
      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

    
      const prev = document.getElementById('mod-cargos-js');
      if (prev) prev.remove();

      const s = document.createElement('script');
      s.id    = 'mod-cargos-js';
      s.src   = 'js/cargos.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Cargos');
    });
}

function cargarModuloClientes() {
  const cont = document.getElementById('contenido');

  fetch('modulos/clientes/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Clientes');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

     
      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

    
      const prev = document.getElementById('mod-clientes-js');
      if (prev) prev.remove();

      const s  = document.createElement('script');
      s.id     = 'mod-clientes-js';
      s.src    = 'js/clientes.js?v=' + Date.now();
      s.defer  = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Clientes');
    });
}


function cargarModuloProductos() {
  const cont = document.getElementById('contenido');

  fetch('modulos/productos/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Productos');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }


      const prev = document.getElementById('mod-productos-js');
      if (prev) prev.remove();

      const s = document.createElement('script');
      s.id    = 'mod-productos-js';
      s.src   = 'js/productos.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Productos');
    });
}

function cargarModuloCompras() {
  const cont = document.getElementById('contenido');

  fetch('modulos/compras/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Compras');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      
      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

      const prev = document.getElementById('mod-compras-js');
      if (prev) prev.remove();

      const s  = document.createElement('script');
      s.id     = 'mod-compras-js';
      s.src    = 'js/compras.js?v=' + Date.now();
      s.defer  = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Compras');
    });
}

window.cargarModuloSalidas = function () {
    const cont = document.getElementById('contenido');

    fetch('modulos/salidas/index.php', { credentials: 'same-origin' })
      .then(r => {
        if (!r.ok) throw new Error('No se pudo cargar Salidas');
        return r.text();
      })
      .then(html => {
        cont.innerHTML = html;

      
        if (!document.querySelector('link[href*="vistas.css"]')) {
          const l = document.createElement('link');
          l.rel   = 'stylesheet';
          l.href  = 'css/vistas.css?v=' + Date.now();
          document.head.appendChild(l);
        }


        const prev = document.getElementById('mod-salidas-js');
        if (prev) prev.remove();

        const s  = document.createElement('script');
        s.id     = 'mod-salidas-js';
        s.src    = 'js/salidas.js?v=' + Date.now(); 
        s.defer  = true;
        document.body.appendChild(s);
      })
      .catch(err => {
        console.error(err);
        alert('Error cargando Salidas de inventario');
      });
  };



  window.cargarModuloNomina = function () {
    const cont = document.getElementById('contenido');

    fetch('modulos/nomina/index.php', { credentials: 'same-origin' })
      .then(r => {
        if (!r.ok) throw new Error('No se pudo cargar Salidas');
        return r.text();
      })
      .then(html => {
        cont.innerHTML = html;

      
        if (!document.querySelector('link[href*="vistas.css"]')) {
          const l = document.createElement('link');
          l.rel   = 'stylesheet';
          l.href  = 'css/vistas.css?v=' + Date.now();
          document.head.appendChild(l);
        }


        const prev = document.getElementById('mod-nomina-js');
        if (prev) prev.remove();

        const s  = document.createElement('script');
        s.id     = 'mod-nomina-js';
        s.src    = 'js/nomina.js?v=' + Date.now(); 
        s.defer  = true;
        document.body.appendChild(s);
      })
      .catch(err => {
        console.error(err);
        alert('Error cargando nominas');
      });
  };

window.cargarModuloInicio = function () {
  const cont = document.getElementById('contenido');
  if (!cont) return;

  fetch('modulos/inicio/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Inicio');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      if (!document.querySelector('link[href*="inicio.css"]')) {
        const l = document.createElement('link');
        l.rel  = 'stylesheet';
        l.href = 'css/inicio.css?v=' + Date.now();
        document.head.appendChild(l);
      }

    })
    .catch(err => {
      console.error(err);
      cont.innerHTML = '<p>Error cargando Inicio</p>';
    });
};

document.addEventListener('DOMContentLoaded', function () {
  if (typeof window.cargarModuloInicio === 'function') {
    window.cargarModuloInicio();
  }
});


// Cargar módulo de Reportes
window.cargarModuloReportes = function () {
  const cont = document.getElementById('contenido');
  if (!cont) return;

  fetch('modulos/reportes/index.php', { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('No se pudo cargar Reportes');
      return r.text();
    })
    .then(html => {
      cont.innerHTML = html;

      if (!document.querySelector('link[href*="vistas.css"]')) {
        const l = document.createElement('link');
        l.rel   = 'stylesheet';
        l.href  = 'css/vistas.css?v=' + Date.now();
        document.head.appendChild(l);
      }

      const prev = document.getElementById('mod-reportes-js');
      if (prev) prev.remove();

      const s  = document.createElement('script');
      s.id     = 'mod-reportes-js';
      s.src    = 'js/reportes.js?v=' + Date.now();
      s.defer  = true;
      document.body.appendChild(s);
    })
    .catch(err => {
      console.error(err);
      alert('Error cargando Reportes');
    });
};

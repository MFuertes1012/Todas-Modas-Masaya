// Pinta contenido y marca activo en el menú principal
function mostrarContenido(seccion){
  const cont = document.getElementById('contenido');
  cont.innerHTML = `<h2>${seccion}</h2>`;

  // Activo en nivel 1
  document.querySelectorAll('.item.nivel1').forEach(li => li.classList.remove('activo'));
  const match = Array.from(document.querySelectorAll('.item.nivel1'))
    .find(li => li.textContent.trim().startsWith(seccion));
  if (match) match.classList.add('activo');
}

document.addEventListener('DOMContentLoaded', () => {
  // Marcar Inicio al cargar
  mostrarContenido('Inicio');

  // Submenús desplegables HACIA ABAJO
  document.querySelectorAll('.submenu').forEach(s => {
    const header = s.querySelector('.submenu-header');
    const list   = s.querySelector('.submenu-list');
    const toggle = s.querySelector('.toggle-icon');

    header.addEventListener('click', (e) => {
      e.stopPropagation();

      // Cierra otros submenús (opcional). Quita este bloque si quieres múltiples abiertos.
      document.querySelectorAll('.submenu.open').forEach(o=>{
        if(o!==s){ 
          o.classList.remove('open'); 
          const t = o.querySelector('.toggle-icon');
          if (t) t.src = 'img/mas.svg';
        }
      });

      const open = s.classList.toggle('open');
      if (toggle) toggle.src = open ? 'img/menos.svg' : 'img/mas.svg';

      // Ya no hay posicionamiento lateral; al ser position:static, el contenido
      // empuja hacia abajo dentro del mismo sidebar.
    });
  });
});

function verPerfil() {
  // Solo mostrar una pantalla de verificación
  mostrarContenido('Información');
}

function cerrarSesion() {
  // Redirige al cierre de sesión (tu archivo existe en /modulos/)
  window.location.href = 'cerrar sesion.php';
  // Si lo renombras sin espacios: 'modulos/cerrar_sesion.php'
}

function cargarModuloEmpleados() {
  // Carga el módulo dentro del <main id="contenido">
  fetch('modulos/empleados/index.php', { credentials: 'same-origin' })
    .then(r => r.text())
    .then(html => {
      const cont = document.getElementById('contenido');
      cont.innerHTML = html;
      // Marcar activo “Empleado” (opcional)
      document.querySelectorAll('.item.nivel1').forEach(li => li.classList.remove('activo'));
      // Carga el JS del módulo (trae eventos del modal/tabla)
      const s = document.createElement('script');
      s.src = 'js/empleados.js?v=' + Date.now();
      s.defer = true;
      document.body.appendChild(s);
      // Carga CSS del módulo si no existe
      if (!document.querySelector('link[href^="css/empleados.css"]')) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'css/empleados.css?v=' + Date.now();
        document.head.appendChild(l);
      }
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

      // Asegurar carga del script del módulo (por innerHTML los <script> no corren)
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

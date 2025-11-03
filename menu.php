<?php
session_start();
if (!isset($_SESSION['correo'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Toda Moda Masaya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/menu.css" />
  <script defer src="js/menu.js"></script>
</head>
<body>
  <!-- Barra superior -->
  <header class="top-bar">
    <h1>TODA MODA MASAYA</h1>

    
  <!-- Íconos de perfil y cerrarSesion -->
  <div class="top-actions">
    <!-- perfil -->
    <img
      src="img/ver_perfil.svg"  
      alt="Ver perfil"
      class="top-icon"
      title="Ver perfil"
      onclick="verPerfil()"
    />

    <!-- Cerrar sesión -->
    <img
      src="img/cerrar_sesion.svg"
      alt="Cerrar sesión"
      class="top-icon"
      title="Cerrar sesión"
      onclick="cerrarSesion()"
    />
  </div>
  </header>

  <!-- Layout principal -->
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile">
        <img src="img/perfil.png" alt="Perfil" class="profile-img">
        <p class="nombre"><?= htmlspecialchars($_SESSION['nombre'] ?? ''); ?></p>
        <p class="correo"><?= htmlspecialchars($_SESSION['correo'] ?? ''); ?></p>
        <hr class="divider">
      </div>

      <!-- Desde aquí hacia abajo: fondo blanco -->
      <div class="nav-section">
        <p class="titulo-menu">NAVEGACIÓN PRINCIPAL</p>

        <nav class="menu">
          <ul>

            <li class="item nivel1 activo" onclick="mostrarContenido('Inicio')">
              <img src="img/inicio.svg" class="icon" alt=""> <span class="label">Inicio</span>
            </li>

               <!-- Clientes -->
             <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/cliente.svg" class="icon" alt=""><span class="label">Clientes</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon" alt="toggle">
              </div>
              <ul class="submenu-list">
                <li class="item" onclick="mostrarContenido('Cliesntes')">Registrar Cliente</li>
                <li class="item" onclick="cargarModuloDepartamentos()">Departamentos</li>
              </ul>
            </li>

            <!-- Empleado -->
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/empleado.svg" class="icon" alt=""><span class="label">Empleado</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon" alt="toggle">
              </div>
              <ul class="submenu-list">
                <li class="item" onclick="cargarModuloEmpleados()">Registrar empleado</li>
                <li class="item" onclick="mostrarContenido('Planilla de pago')">Planilla de pago</li>
              </ul>
            </li>

            <!-- Gestión de productos -->
           <li class="submenu">
  <div class="submenu-header">
    <img src="img/inventario.svg" class="icon"> <span class="label">Gestión de productos</span>
    <img src="img/mas.svg" class="toggle-icon">
  </div>
  <ul class="submenu-list">
    <li class="item" onclick="mostrarContenido('Productos')">Productos</li>
    <li class="item" onclick="cargarModuloCategorias()">Categorías</li>
    <li class="item" onclick="cargarModuloProveedores()">Ingresar proveedor</li>

  </ul>
</li>

            <!-- Gestión de mercancia -->
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/mercancia.svg" class="icon" alt=""><span class="label">Gestión de mercancia</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon" alt="toggle">
              </div>
              <ul class="submenu-list">
                <li class="item" onclick="mostrarContenido('Compras')">Compras</li>
                <li class="item" onclick="mostrarContenido('Salida de inventario')">Salida de inventario</li>
              </ul>
            </li>

            <!-- Gestión de usuarios -->
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/usuario.svg" class="icon" alt=""><span class="label">Gestión de usuarios</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon" alt="toggle">
              </div>
              <ul class="submenu-list">
                <li class="item" onclick="cargarModuloUsuarios()">Usuarios</li>
                 <li class="item" onclick="cargarModuloRoles()">Roles</li>

              </ul>
            </li>

            <li class="item nivel1" onclick="mostrarContenido('Reportes')">
              <img src="img/reporte.svg" class="icon" alt=""> <span class="label">Reportes</span>
            </li>

          </ul>
        </nav>
      </div>
    </aside>

    <!-- Contenido -->
    <main id="contenido">
      <h2>Inicio</h2>
    </main>
  </div>
</body>
</html>

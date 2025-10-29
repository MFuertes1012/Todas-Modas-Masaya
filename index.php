<?php
session_start();
include("conexion.php");

// Lee y limpia mensaje de error (flash)
$error = "";
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    // OJO: usa md5 porque así tienes guardadas las contraseñas.
    $hash = md5($contrasena);

    // Consulta preparada
    $stmt = $conexion->prepare("SELECT IdUsuario, Nombre_de_Usuario, Correo FROM Usuario WHERE Correo = ? AND Contrasena = ?");
    $stmt->bind_param("ss", $correo, $hash);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();

        // Usa SIEMPRE los mismos keys que leerás en menu.php
        session_regenerate_id(true);
        $_SESSION['id']     = $row['IdUsuario'];
        $_SESSION['nombre'] = $row['Nombre_de_Usuario'];
        $_SESSION['correo'] = $row['Correo'];

        // Redirige al menú (no a holamundo.php)
        header("Location: menu.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Correo o contraseña incorrectos.";
        header("Location: index.php"); // PRG
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión - TodaModaMasayaWeb</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="img/logo.jpg" alt="Logo de la tienda" class="logo">
        </div>
        <h2>Iniciar Sesión</h2>
        <form method="POST" id="loginForm" novalidate>
            <div class="input-group">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="email" name="correo" placeholder="Correo" required>
            </div>
            <div class="input-group">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                <ion-icon name="eye-outline" id="togglePassword" class="eye-icon"></ion-icon>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div class="toast" id="toast">
            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <button id="closeToast" type="button">Aceptar</button>
        </div>
    <?php endif; ?>

    <script src="js/script.js"></script>
</body>
</html>

// Toggle contraseña
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('contrasena');

if (togglePassword && password) {
  togglePassword.addEventListener('click', () => {
    const isPassword = password.getAttribute('type') === 'password';
    password.setAttribute('type', isPassword ? 'text' : 'password');
    togglePassword.setAttribute('name', isPassword ? 'eye-off-outline' : 'eye-outline');
  });
}

// Cerrar toast al hacer clic en Aceptar
const closeToast = document.getElementById('closeToast');
const toast = document.getElementById('toast');

if (closeToast && toast) {
  closeToast.addEventListener('click', () => {
    toast.style.display = 'none';
  });
}

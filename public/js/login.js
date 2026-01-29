// public/js/login.js

// Ejemplo: cerrar alertas automáticamente después de 5 segundos
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
        }, 5000);
    });
});

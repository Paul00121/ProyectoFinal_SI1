document.addEventListener('DOMContentLoaded', () => {
    const nombre = document.getElementById('nombre');
    const apellidos = document.getElementById('apellidos');
    const form = document.getElementById('register-form');
    const password = document.getElementById('password');

    function capitalizarPalabras(input) {
        input.addEventListener('input', () => {
            // Guardar posición del cursor
            const start = input.selectionStart;

            // Capitalizar cada palabra, mantener espacios
            let valor = input.value;

            valor = valor.replace(/[^A-Za-záéíóúÁÉÍÓÚñÑ\s]/g, ''); // solo letras y espacios
            valor = valor.replace(/\s+/g, ' '); // reemplaza múltiples espacios por uno
            valor = valor.split(' ').map(p => {
                if (p.length === 0) return '';
                return p.charAt(0).toUpperCase() + p.slice(1).toLowerCase();
            }).join(' ');

            input.value = valor;

            // Restaurar posición del cursor
            input.setSelectionRange(start, start);
        });
    }

    capitalizarPalabras(nombre);
    capitalizarPalabras(apellidos);

    // Validación al enviar formulario
    form.addEventListener('submit', (e) => {
        let valid = true;
        const regexLetrasEspacios = /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/;

        if (!regexLetrasEspacios.test(nombre.value)) {
            alert("El nombre solo puede contener letras y espacios.");
            valid = false;
        }

        if (!regexLetrasEspacios.test(apellidos.value)) {
            alert("Los apellidos solo pueden contener letras y espacios.");
            valid = false;
        }

        // Contraseña: máximo 8 caracteres y al menos 1 carácter especial
        const regexPassword = /[!@#$%^&*(),.?":{}|<>]/;
        if (password.value.length > 8 || !regexPassword.test(password.value)) {
            alert("La contraseña debe tener máximo 8 caracteres y al menos un carácter especial.");
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});

const producto = document.getElementById('producto_id');
const cantidad = document.getElementById('cantidad');
const total = document.getElementById('total');

function calcular() {
    const precio = parseFloat(producto.selectedOptions[0]?.dataset.precio || 0);
    const cant = parseInt(cantidad.value) || 1;
    total.value = (precio * cant).toFixed(2);
}

producto.addEventListener('change', calcular);
cantidad.addEventListener('input', calcular);

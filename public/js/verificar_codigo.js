document.addEventListener('DOMContentLoaded', () => {
    const countdownEl = document.getElementById('countdown');
    const codigoInput = document.querySelector('input[name="codigo"]');
    const verifyBtn = document.getElementById('verify-btn');

    let tiempo = parseInt(countdownEl.textContent);

    const intervalo = setInterval(() => {
        if (tiempo > 0) {
            tiempo--;
            countdownEl.textContent = tiempo;
        } else {
            clearInterval(intervalo);
            countdownEl.textContent = "0";
            codigoInput.disabled = true;
            verifyBtn.textContent = "Volver a Enviar Código";
            verifyBtn.onclick = (e) => {
                e.preventDefault();
                window.location.href = "recuperar.php";
            };
        }
    }, 1000);
});

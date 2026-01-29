// Temporizador de 60 segundos para reenvío del código
document.addEventListener("DOMContentLoaded", function() {
    const resendInfo = document.getElementById("resend-info");
    const resendBtn = document.getElementById("resend-btn");

    let countdown = 60; // segundos

    function updateCountdown() {
        if (countdown > 0) {
            resendInfo.textContent = `Puedes reenviar el código en ${countdown} segundos`;
            resendBtn.disabled = true;
            countdown--;
        } else {
            resendInfo.textContent = "";
            resendBtn.disabled = false;
            clearInterval(timer);
        }
    }

    if(resendInfo && resendBtn){
        updateCountdown(); // inicial
        var timer = setInterval(updateCountdown, 1000);
    }
});

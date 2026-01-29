document.addEventListener("DOMContentLoaded", () => {

    const cards = document.querySelectorAll(".product-card");

    cards.forEach((card, i) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";

        setTimeout(() => {
            card.style.transition = "opacity .4s ease, transform .4s ease";
            card.style.opacity = "1";
            card.style.transform = "";
        }, i * 80);
    });

    /* AUTO BUSCADOR */
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        let timer;
        searchInput.addEventListener("input", () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                searchInput.form.submit();
            }, 400);
        });
    }

});

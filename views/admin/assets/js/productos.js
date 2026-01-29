document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector(".search-box");
    const form = searchInput.closest("form");

    searchInput.addEventListener("input", () => {
        if (form.timeout) clearTimeout(form.timeout);
        form.timeout = setTimeout(() => {
            form.submit();
        }, 500);
    });
});

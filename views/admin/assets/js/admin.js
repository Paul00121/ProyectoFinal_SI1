// admin.js - scripts globales para el admin
document.addEventListener('DOMContentLoaded', () => {
    // ejemplo: pequeño handler para links del sidebar (puedes ampliar)
    document.querySelectorAll('.sidebar .nav-link').forEach(a => {
        a.addEventListener('click', () => {
            document.querySelectorAll('.sidebar .nav-link').forEach(x => x.classList.remove('active'));
            a.classList.add('active');
        });
    });
});

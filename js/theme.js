// js/theme.js (o dentro de tu script en index.php)

// 1. Al cargar la página: Comprobar LocalStorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme'); // Leemos la memoria del navegador
    const btnIcon = document.getElementById('themeIcon');

    // Si el usuario guardó "light", activamos el modo claro
    if (savedTheme === 'light') {
        document.body.classList.add('light-mode');
        if(btnIcon) {
            btnIcon.classList.remove('fa-moon');
            btnIcon.classList.add('fa-sun'); // Cambiamos icono a Sol
        }
    }
});

// 2. Función para cambiar el tema (llamada por el botón)
function toggleTheme(e) {
    if(e) e.preventDefault(); // Evitar que el enlace recargue la página

    const body = document.body;
    const btnIcon = document.getElementById('themeIcon');

    // Alternar la clase
    body.classList.toggle('light-mode');

    // Comprobar qué modo quedó activo
    if (body.classList.contains('light-mode')) {
        // MODO CLARO ACTIVADO
        localStorage.setItem('theme', 'light'); // Guardamos en LocalStorage
        if(btnIcon) {
            btnIcon.classList.remove('fa-moon');
            btnIcon.classList.add('fa-sun');
        }
    } else {
        // MODO OSCURO ACTIVADO
        localStorage.setItem('theme', 'dark'); // Guardamos en LocalStorage
        if(btnIcon) {
            btnIcon.classList.remove('fa-sun');
            btnIcon.classList.add('fa-moon');
        }
    }
}
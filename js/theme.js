


document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    const btnIcon = document.getElementById('themeIcon');


    if (savedTheme === 'light') {
        document.body.classList.add('light-mode');
        if (btnIcon) {
            btnIcon.classList.remove('fa-moon');
            btnIcon.classList.add('fa-sun');
        }
    }
});


function toggleTheme(e) {
    if (e) e.preventDefault();

    const body = document.body;
    const btnIcon = document.getElementById('themeIcon');


    body.classList.toggle('light-mode');


    if (body.classList.contains('light-mode')) {

        localStorage.setItem('theme', 'light');
        if (btnIcon) {
            btnIcon.classList.remove('fa-moon');
            btnIcon.classList.add('fa-sun');
        }
    } else {

        localStorage.setItem('theme', 'dark');
        if (btnIcon) {
            btnIcon.classList.remove('fa-sun');
            btnIcon.classList.add('fa-moon');
        }
    }
}
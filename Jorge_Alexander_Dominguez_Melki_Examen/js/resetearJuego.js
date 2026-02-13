// Seleccionamos el botón de reset y le añadimos un evento para reiniciar el juego
const botonReset = document.getElementById("btn-reset");
// Si el botón existe, le asignamos la función de reiniciar el juego al hacer clic
if (botonReset) {
    botonReset.onclick = function() {
        // Preguntamos al usuario antes de borrar nada
        const confirmacion = confirm("¿Seguro que quieres reiniciar el juego?");
        // Si el usuario confirma, reiniciamos el juego pintando el tablero y eligiendo un nuevo personaje secreto
        if (confirmacion) {
            // Limpiamos el tablero y volvemos a pintar todo desde cero
            pintarTablero();
            // Elegimos un nuevo personaje secreto para el juego
            elegirSecreto();
        }
    };
}
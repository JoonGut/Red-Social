// hacemos la funcion elegirsecreto
function elegirSecreto() {
// Seleccionamos el contenedor donde se mostrará el personaje secreto
    const contenedor = document.getElementById("personaje-secreto");
    if (!contenedor) return;

    // Elegimos un personaje al azar de tu array
    const elegido = personajes[Math.floor(Math.random() * personajes.length)];

    // Mostramos la imagen y el nombre en el contenedor de personaje secreto
    contenedor.innerHTML = `
        <div>
            <img src="imagenes/${elegido.ruta}" alt="${elegido.nombre}" style="width: 150px; border: 3px solid black; ">
            <p style="font-size: 1.5rem; font-weight: bold; margin-top: 10px;">${elegido.nombre}</p>
        </div>
    `;
}
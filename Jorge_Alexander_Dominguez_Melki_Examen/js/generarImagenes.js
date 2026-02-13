// hacemos la funcion de pintarTablero
function pintarTablero() {
    // Seleccionamos el contenedor del tablero
    const contenedor = document.getElementById("tablero-juego");
    if (!contenedor) return;
    
    // Limpiamos el tablero antes de pintar
    contenedor.innerHTML = "";

    // Mezclamos el array de personajes para que cada vez salgan en orden diferente
    personajes.sort(() => Math.random() - 0.5);

    // Recorremos el array de personajes para crear las cartas
    personajes.forEach(pje => {
        // Creamos el contenedor de la "carta"
        const divCarta = document.createElement("div");
        
        // Creamos la imagen del personaje y le ponemos la ruta de la imagen
        const img = document.createElement("img");
        img.src = "imagenes/" + pje.ruta;
        
        // cambia entre el personaje y Miyazaki
        img.onclick = () => {
            if (img.src.includes("miyazaki.jpeg")) {
                img.src = "imagenes/" + pje.ruta;
            } else {
                img.src = "imagenes/miyazaki.jpeg";
            }
        };

        // Creamos el nombre del personaje para mostrar debajo de la imagen
        const nombreParaMostrar = document.createElement("p");
        nombreParaMostrar.innerText = pje.nombre;
        nombreParaMostrar.style.fontWeight = "bold";
        nombreParaMostrar.style.marginTop = "5px";

        // Metemos la imagen y el nombre dentro de la carta
        divCarta.appendChild(img);
        divCarta.appendChild(nombreParaMostrar);

        // Metemos la carta completa en el tablero
        contenedor.appendChild(divCarta);
    });
}
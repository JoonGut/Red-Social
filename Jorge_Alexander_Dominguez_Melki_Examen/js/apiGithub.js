async function cargarGithub() {
    //Ponemos aquí mi nombre de usuario real del Github
    const miUsuario = "Jorge-lang-de";
    

    try {
        // Hacemos la petición a la API de GitHub y esperamos la respuesta
        const respuesta = await fetch("https://api.github.com/users/" + miUsuario);
        // Convertimos la respuesta a JSON que seria un objeto con toda la info de mi perfil
        const datos = await respuesta.json();
        // Ahora los datos es un objeto con toda la info de mi perfil
        const footer = document.getElementById("github-info");
        
        if (footer) {
            // ponemos en el HTML: El enlace <a> que envuelve a la imagen y el nombre y si nos daria error nos pondra un mensaje
            footer.innerHTML = `
                
                    <br>
                    <a href="${datos.html_url}" target="_blank" title="Ver mi perfil de GitHub">
                        <img src="${datos.avatar_url}" alt="Avatar" style="width: 180px;  solid #333;">
                        <p><strong>${datos.login}(${datos.public_repos})</strong></p>
                    </a>
                    
                </div>
            `;
        }
    } catch (error) {
        console.error("Error cargando la API de GitHub:", error);
    }
}

// ejecuta toda la página y sus recursos para comenzar el juego
window.onload = function() {
    // Dibuja el tablero con los nombres y las imágenes de los personajes
    if (typeof pintarTablero === "function") pintarTablero();
    
    // Elige un personaje secreto de la imagen de arriba
    if (typeof elegirSecreto === "function") elegirSecreto();
    
    // y por ultimo carga la informacion de GitHub en el footer
    cargarGithub();
};
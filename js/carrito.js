const botones = document.querySelectorAll('#boton_comprar');
const contadorCarro = document.getElementById('cuenta_carro');

let carrito = JSON.parse(localStorage.getItem("carrito") || "[]");
contadorCarro.textContent = carrito.length;

botones.forEach(boton => {
  boton.dataset.agregado = 'false';

  boton.addEventListener('click', () => {
    const contenedor = boton.parentElement;

    const titulo =
      contenedor.querySelector('p#principioss, p#principio, p#principios')?.textContent
      || "Libro sin título";

    const precioTexto = [...contenedor.querySelectorAll('p')]
      .find(p => p.textContent.includes("Precio"));

    const precio = precioTexto
      ? parseFloat(precioTexto.textContent.replace("Precio:", "").replace("€", "").replace(",", "."))
      : 0;

    const autor = contenedor.querySelector('p#autor')?.textContent || "Desconocido";
    const categoria = contenedor.querySelector('p#categoria')?.textContent || "General";
    const isbn = contenedor.querySelector('p#isbn')?.textContent.replace("ISBN:", "").trim() || "0000000000";
    const editorial = contenedor.querySelector('p#editorial')?.textContent.replace("Editorial:", "").trim() || "Desconocida";

    if (boton.dataset.agregado === 'false') {
      carrito.push({ titulo, precio, autor, categoria, editorial, isbn, cantidad: 1 });
      boton.dataset.agregado = 'true';
      boton.textContent = 'Quitar';
    } else {
      carrito = carrito.filter(item => item.titulo !== titulo);
      boton.dataset.agregado = 'false';
      boton.textContent = 'Comprar';
    }

    localStorage.setItem("carrito", JSON.stringify(carrito));
    contadorCarro.textContent = carrito.length;
  });
});

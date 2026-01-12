document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');
    if (!form) return;
    // Busca el span de error que sigue al input
    function getErrorSpan(el) {
        let sibling = el.nextElementSibling;
        while (sibling) {
            if (sibling.classList && sibling.classList.contains('error-msg')) return sibling;
            sibling = sibling.nextElementSibling;
        }
        return null;
    }
 
    function LimpiarError(el) {
        const span = getErrorSpan(el);
        if (span){
            span.textContent = '';
        } 
        el.classList.remove('invalid');
    }

    function setError(el, msg) {
        const span = getErrorSpan(el);
        if (span){
            span.textContent = msg;
        }
        el.classList.add('invalid');
        
    }



    function validaUsuario(val) {
        if (!val){
            return 'El usuario es obligatorio.';
        }
    }

    /*function validaDNI(val) {
        if (!val){
            return 'El DNI es obligatorio.';
        } 
        val = val.trim().toUpperCase();
        let letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        if (!/^\d{7,8}[A-Z]$/.test(val)) {
            return 'Formato de DNI inválido.';
        }
        let numberPart = val.substring(0, val.length - 1);
        let number = parseInt(numberPart, 10);
        let expected = letters[number % 23];
        let letter = val.charAt(val.length - 1);
        if (expected !== letter) {
            return 'Letra del DNI incorrecta.';
        }
    }*/
   


    function validaPassword(val) {
        if (!val){
            return 'La contraseña es obligatoria.';
        }else{
            if (val.length < 12){
                return 'Debe tener al menos 12 caracteres.';
            }else{
                //Debe contener números y símbolos
                if (!/[0-9]/.test(val) || !/[!@#%^&*]/.test(val)){
                    return 'La contraseña debe contener números y símbolos.';
                } 
            }
        }
        
    }




    form.addEventListener('submit', (e) => {
        let valido = true;

        // Campos
        const campos = {
            usuario: form.querySelector('#usuario'),
            password: form.querySelector('#password'),
        };

        // Limpiar todos los errores
        Object.values(campos).forEach(campo => LimpiarError(campo));


        let msg;
        
        if ((msg = validaUsuario(campos.usuario.value))) {
             setError(campos.usuario, msg); valido = false; 
        }
        if ((msg = validaPassword(campos.password.value))) {
             setError(campos.password, msg); valido = false; 
        }
        // Bloquear envío si hay errores
        if (!valido){
            e.preventDefault();
            alert('Hay errores en el formulario. Por favor, revíselo.');
        }else{
            alert('Formulario enviado correctamente.');
        }
    });

});
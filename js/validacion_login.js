document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');
    if (!form) return;
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
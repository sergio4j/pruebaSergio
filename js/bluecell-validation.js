jQuery(document).ready(function($) {

    $('#bluecell_form').submit(function(event) {
        event.preventDefault();

        let isValid = true;

        if ($('#nombre').val().trim() === '') {
            isValid = false;
            alert('Nombre es requerido.');
        }

        if ($('#email').val().trim() === '') {
            isValid = false;
            alert('Email es requerido.');
        }

        if ($('#telefono').val().trim() === '') {
            isValid = false;
            alert('Teléfono es requerido.');
        }

        if ($('#mensaje').val().trim() === '') {
            isValid = false;
            alert('Mensaje es requerido.');
        }

        if ($('#asunto').val().trim() === '') {
            isValid = false;
            alert('Asunto es requerido.');
        }

        if (!$('#aceptacion').is(':checked')) {
            isValid = false;
            alert('Debes aceptar las políticas.');
        }

        if (!isValid) {
            return;
        }

        let data = {
            'action': 'bluecell_form_submit',
            'nombre': $('#nombre').val(),
            'email': $('#email').val(),
            'telefono': $('#telefono').val(),
            'mensaje': $('#mensaje').val(),
            'asunto': $('#asunto').val()
        };
        this.reset();
        $.post(bluecellAjax.ajaxurl, data, function(response) {
            alert('Formulario enviado con éxito.');
        });
    });
});
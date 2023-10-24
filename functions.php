<?php
// Creación de la tabla en la activación
register_activation_hook(plugin_dir_path(__DIR__) . 'formulario-bluecell.php', 'bluecell_create_table');
function bluecell_create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'bluecell_form';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        nombre varchar(255) DEFAULT '' NOT NULL,
        email varchar(255) DEFAULT '' NOT NULL,
        telefono varchar(100) DEFAULT '' NOT NULL,
        mensaje text NOT NULL,
        asunto varchar(255) DEFAULT '' NOT NULL,
        aceptacion tinyint(1) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Borrado de la tabla en la desactivación
register_deactivation_hook(__FILE__, 'bluecell_remove_table');
function bluecell_remove_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bluecell_form';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Enqueuing scripts
function enqueue_bluecell_scripts() {
    wp_enqueue_script('bluecell-validation', plugin_dir_url(__FILE__) . 'js/bluecell-validation.js', array('jquery'), '1.0', true);

    wp_localize_script('bluecell-validation', 'bluecellAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_bluecell_scripts');

function bluecell_enqueue_styles() {
    $css_path = plugins_url( 'css/bluecell-styles.css', __FILE__ );

    wp_register_style( 'bluecell-styles', $css_path );
    wp_enqueue_style( 'bluecell-styles' );
}
add_action( 'wp_enqueue_scripts', 'bluecell_enqueue_styles' );
// Manejando el envío de AJAX
function bluecell_form_submit() {
    global $wpdb;

    // Sanitizar los datos
    $nombre = sanitize_text_field($_POST['nombre']);
    $email = sanitize_email($_POST['email']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $mensaje = sanitize_textarea_field($_POST['mensaje']);
    $asunto = sanitize_text_field($_POST['asunto']);
    // Añade la sanitización de otros campos si es necesario...

    $table_name = $wpdb->prefix . 'bluecell_form';
    $wpdb->insert($table_name, array(
        'time' => current_time('mysql'),
        'nombre' => $nombre,
        'email' => $email,
        'telefono' => $telefono,
        'mensaje' => $mensaje,
        'asunto' => $asunto,
        // Añade otros campos aquí si es necesario...
    ));

    echo 'success';
    wp_die();
}
add_action('wp_ajax_bluecell_form_submit', 'bluecell_form_submit'); 
add_action('wp_ajax_nopriv_bluecell_form_submit', 'bluecell_form_submit');

function bluecell_add_form($content) {
    if(is_single()) {
        $form = '
            <form id="bluecell_form" method="post">
                <div>
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" maxlength="9" required>
                </div>
                <div>
                    <label for="asunto">Asunto:</label>
                    <input type="text" id="asunto" name="asunto" required>
                </div>
                <div>
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" name="mensaje" required></textarea>
                </div>
                <div>
                    <input type="checkbox" id="aceptacion" name="aceptacion" required>
                    <label for="aceptacion">Acepto las políticas</label>
                </div>
                <div>
                    <input type="submit" value="Enviar">
                </div>
            </form>
        ';

        $content .= $form;
    }
    return $content;
}

add_filter('the_content', 'bluecell_add_form');


//Mostrar en el admin 

function bluecell_admin_menu() {
    add_menu_page(
        'Formulario Bluecell',     
        'Formulario Bluecell',      
        'manage_options',           
        'bluecell_form_data',       
        'bluecell_display_data',    
        'dashicons-feedback',      
        25                          
    );
}
add_action('admin_menu', 'bluecell_admin_menu');


function bluecell_display_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bluecell_form';
    $entries = $wpdb->get_results("SELECT * FROM $table_name");
    $logo_url = 'https://bluecell.es/firma/logo.gif'; 
    
    echo '<div class="wrap">';
    echo '<img src="' . $logo_url . '" alt="Bluecell Logo" style="display: block; margin: 20px 0;">';
    echo '<div class="wrap">';
    echo '<h2>Formulario Bluecell Contactos</h2>';
    echo '<table id="bluecell_data_table" class="display table table-bordered table-striped">';
    echo '<thead><tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Mensaje</th><th>Asunto</th></tr></thead>';
    echo '<tbody>';

    foreach ($entries as $entry) {
        echo '<tr>';
        echo '<td>' . esc_html($entry->time) . '</td>';
        echo '<td>' . esc_html($entry->nombre) . '</td>';
        echo '<td>' . esc_html($entry->email) . '</td>';
        echo '<td>' . esc_html($entry->telefono) . '</td>';
        echo '<td>' . esc_html($entry->mensaje) . '</td>';
        echo '<td>' . esc_html($entry->asunto) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    $entries = $wpdb->get_results("SELECT * FROM $table_name");

}

// Cargar Datatable y Traducir, prueba solo cargo de cdn, para que sea mas bonito uso el de Boostrap

function bluecell_admin_enqueue_scripts($hook) {
    if ('toplevel_page_bluecell_form_data' !== $hook) {
        return;
    }
    wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_style('datatables-bootstrap', 'https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap4.min.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js', array('jquery'), '1.10.23', true);
    wp_enqueue_script('datatables-bootstrap', 'https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap4.min.js', array('jquery', 'datatables', 'bootstrap'), '1.10.23', true);

    $datatable_translation = '
        jQuery(document).ready(function() {
            jQuery("#bluecell_data_table").DataTable({
                "language": {
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });
        });
    ';

    wp_add_inline_script('datatables-bootstrap', $datatable_translation);
}
add_action('admin_enqueue_scripts', 'bluecell_admin_enqueue_scripts');
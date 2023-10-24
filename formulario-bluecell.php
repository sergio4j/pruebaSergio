<?php
/**
 * Plugin Name: Formulario Bluecell
 * Description: Plugin para añadir formulario después de the_content en single.php
 * Version: 1.0
 * Author: Sergio Rodríguez Siles
 */

// Requerir el archivo de funciones
require_once plugin_dir_path(__FILE__) . 'functions.php';

register_activation_hook(__FILE__, 'bluecell_create_table');

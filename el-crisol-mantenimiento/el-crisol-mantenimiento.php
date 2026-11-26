<?php
/**
 * Plugin Name: El Crisol - mantenimiento
 * Description: Activa o desactiva la página de mantenimiento de El Crisol desde Ajustes, sin otros plugins.
 * Version: 2.0.1
 * Requires at least: 4.7
 * Requires PHP: 5.6
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

function elcrisol_maintenance_enabled() {
    return (bool) get_option('elcrisol_maintenance_enabled', false);
}

function elcrisol_maintenance_sanitize($value) {
    return $value === true || $value === 1 || $value === '1';
}

function elcrisol_maintenance_register_setting() {
    register_setting('elcrisol_maintenance', 'elcrisol_maintenance_enabled', array(
        'type' => 'boolean',
        'sanitize_callback' => 'elcrisol_maintenance_sanitize',
        'default' => false,
    ));
}

function elcrisol_maintenance_admin_menu() {
    add_options_page(
        'Mantenimiento El Crisol',
        'Mantenimiento El Crisol',
        'manage_options',
        'elcrisol-mantenimiento',
        'elcrisol_maintenance_settings_page'
    );
}

function elcrisol_maintenance_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Mantenimiento El Crisol</h1>
        <?php settings_errors(); ?>
        <p>Activa la página amarilla con el logo y los datos de contacto mientras reparas el sitio.</p>
        <form action="options.php" method="post">
            <?php settings_fields('elcrisol_maintenance'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Modo mantenimiento</th>
                    <td>
                        <input type="hidden" name="elcrisol_maintenance_enabled" value="0">
                        <label for="elcrisol-maintenance-enabled">
                            <input type="checkbox" id="elcrisol-maintenance-enabled" name="elcrisol_maintenance_enabled" value="1" <?php checked(elcrisol_maintenance_enabled()); ?>>
                            Mostrar la página de mantenimiento
                        </label>
                        <p class="description">Los administradores con sesión iniciada pueden seguir viendo y reparando la web. Para revisar la página de mantenimiento, abre el sitio en una ventana de incógnito.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar cambios'); ?>
        </form>
        <p>Después de cambiar esta opción, borra la caché del sitio si utilizas un servicio de caché.</p>
    </div>
    <?php
}

function elcrisol_maintenance_action_links($links) {
    array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=elcrisol-mantenimiento')) . '">Ajustes</a>');
    return $links;
}

function elcrisol_maintenance_deactivate() {
    update_option('elcrisol_maintenance_enabled', false);
}

function elcrisol_maintenance_template($template) {
    if (is_admin() || current_user_can('manage_options') || !elcrisol_maintenance_enabled()) {
        return $template;
    }

    status_header(503);
    nocache_headers();
    if (!headers_sent()) {
        header('Retry-After: 3600');
    }

    return dirname(__FILE__) . '/template.php';
}

add_action('admin_init', 'elcrisol_maintenance_register_setting');
add_action('admin_menu', 'elcrisol_maintenance_admin_menu');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'elcrisol_maintenance_action_links');
add_filter('template_include', 'elcrisol_maintenance_template', 1000000);
register_deactivation_hook(__FILE__, 'elcrisol_maintenance_deactivate');

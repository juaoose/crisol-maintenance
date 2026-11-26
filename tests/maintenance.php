<?php
// Exercise the plugin in PHP with the WordPress API boundary represented by stubs.
define('ABSPATH', '/wordpress/');
$options = array();
$actions = array();
$filters = array();
$admin = false;
$can_manage = false;
$status = null;
$no_cache = false;
$checks = 0;
function get_option($key, $default = false) { global $options; return isset($options[$key]) ? $options[$key] : $default; }
function update_option($key, $value) { global $options; $options[$key] = $value; }
function delete_option($key) { global $options; unset($options[$key]); }
function add_action($name, $callback, $priority = 10) { global $actions; $actions[$name] = $callback; }
function add_filter($name, $callback, $priority = 10) { global $filters; $filters[$name] = array($callback, $priority); }
function register_deactivation_hook($file, $callback) { $GLOBALS['deactivate'] = $callback; }
function plugin_basename($file) { return 'el-crisol-mantenimiento/' . basename($file); }
function is_admin() { return $GLOBALS['admin']; }
function current_user_can($capability) { return $capability === 'manage_options' && $GLOBALS['can_manage']; }
function status_header($code) { $GLOBALS['status'] = $code; }
function nocache_headers() { $GLOBALS['no_cache'] = true; }
function register_setting($group, $key, $args) { $GLOBALS['setting'] = array($group, $key, $args); }
function add_options_page($title, $label, $capability, $slug, $callback) { $GLOBALS['menu'] = array($title, $label, $capability, $slug, $callback); }
function esc_url($url) { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
function admin_url($path) { return 'https://example.test/wp-admin/' . $path; }
function plugins_url($path, $file) { return 'https://example.test/wp-content/plugins/el-crisol-mantenimiento/' . $path; }
function settings_errors() {}
function settings_fields($group) { $GLOBALS['nonce_group'] = $group; echo '<input name="_wpnonce" value="test-nonce">'; }
function checked($value) { if ($value) { echo 'checked="checked"'; } }
function submit_button($text) { echo '<button>' . $text . '</button>'; }
function wp_die($message) { throw new RuntimeException($message); }
function verify($condition, $message) { global $checks; if (!$condition) { throw new RuntimeException($message); } $checks++; }

require dirname(__DIR__) . '/el-crisol-mantenimiento/el-crisol-mantenimiento.php';
$original = '/theme/index.php';
$maintenance = dirname(__DIR__) . '/el-crisol-mantenimiento/template.php';
verify(!class_exists('cdp_mweb\\ModoMantenimiento'), 'No original plugin should be loaded.');
verify(!elcrisol_maintenance_enabled(), 'Maintenance must default to off.');
verify(elcrisol_maintenance_template($original) === $original, 'Off must preserve the normal site.');
verify($status === null && !$no_cache, 'Off must not alter response status or cache headers.');

call_user_func($actions['admin_init']);
verify($setting[0] === 'elcrisol_maintenance' && $setting[1] === 'elcrisol_maintenance_enabled', 'Setting must use its own group and option.');
verify($setting[2]['default'] === false && is_callable($setting[2]['sanitize_callback']), 'Setting must have an off default and sanitizer.');
foreach (array(true, 1, '1') as $value) { verify(elcrisol_maintenance_sanitize($value), 'Valid on value rejected.'); }
foreach (array(false, 0, '0', '', null, array('1'), 'false') as $value) { verify(!elcrisol_maintenance_sanitize($value), 'Invalid or off value enabled maintenance.'); }
call_user_func($actions['admin_menu']);
verify($menu[2] === 'manage_options', 'Settings menu must require administrator capability.');
verify($menu[3] === 'elcrisol-mantenimiento', 'Settings slug must remain stable.');

$options['elcrisol_maintenance_enabled'] = true;
verify(elcrisol_maintenance_template($original) === $maintenance, 'Standalone on must serve our template without Carlos Doral plugin.');
verify($status === 503 && $no_cache, 'Maintenance must be a temporary 503 with no-cache headers.');
$can_manage = true;
$status = null;
verify(elcrisol_maintenance_template($original) === $original && $status === null, 'Administrators must retain access to the normal site.');
ob_start();
elcrisol_maintenance_settings_page();
$settings_html = ob_get_clean();
verify(strpos($settings_html, 'action="options.php"') !== false && $nonce_group === 'elcrisol_maintenance', 'Settings must use WordPress protected options submission.');
verify(strpos($settings_html, 'value="0"') !== false && strpos($settings_html, 'value="1"') !== false, 'Form must support both off and on.');
verify(strpos($settings_html, 'checked="checked"') !== false, 'Enabled state must be shown correctly.');
$can_manage = false;
ob_start();
elcrisol_maintenance_settings_page();
verify(ob_get_clean() === '', 'Unauthorized users must receive no settings form.');
$admin = true;
verify(elcrisol_maintenance_template($original) === $original, 'Dashboard requests must not receive the public template.');
$admin = false;
$options['elcrisol_maintenance_enabled'] = false;
verify(elcrisol_maintenance_template($original) === $original, 'Switching off must restore the normal site.');
$options['elcrisol_maintenance_enabled'] = true;
call_user_func($deactivate);
verify(!elcrisol_maintenance_enabled(), 'Deactivating must reset the switch to off for future activation.');

ob_start();
require $maintenance;
$html = ob_get_clean();
verify(strpos($html, 'mailto:elcrisol.cali@gmail.com') !== false, 'Current email missing.');
verify(substr_count($html, 'tel:') === 1 && strpos($html, '387') === false, 'Only the approved phone may appear.');
verify(strpos($html, 'google.com/maps/search/') !== false, 'Address must retain Maps link.');
verify(strpos($html, '/wp-content/plugins/el-crisol-mantenimiento/assets/logo-transparent.png') !== false, 'Logo URL must be independent of the request path and other plugins.');
verify(strpos($html, 'styles.css?ver=2.0.1') !== false, 'Styles must use the new plugin version.');
$options['unrelated_setting'] = 'keep';
define('WP_UNINSTALL_PLUGIN', true);
require dirname(__DIR__) . '/el-crisol-mantenimiento/uninstall.php';
verify(!isset($options['elcrisol_maintenance_enabled']), 'Uninstall must remove its own setting.');
verify($options['unrelated_setting'] === 'keep', 'Uninstall must preserve other site options.');
echo 'PASS: ' . $checks . ' behavior checks on PHP ' . PHP_VERSION . "\n";

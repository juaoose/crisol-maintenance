<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('elcrisol_maintenance_enabled');

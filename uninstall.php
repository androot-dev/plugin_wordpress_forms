<?php

// Si no se está ejecutando desde el administrador de plugins, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'controllers/CallbacksController.php';
require_once plugin_dir_path(__FILE__) . 'controllers/PluginController.php';

$the_plugin = new PluginController();

$the_plugin->uninstall();
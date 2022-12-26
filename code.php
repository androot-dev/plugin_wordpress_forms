<?php
/*
Plugin Name: Custom IU Forms
Description: A custom plugin that adds a sidebar menu item for displaying published forms.
Version: 1.0
Author: Silvera Enterprises
*/


require_once 'controllers/RoutesController.php';
require_once 'controllers/PluginController.php';
require_once 'controllers/CallbacksController.php';
require_once 'controllers/HooksController.php';
require_once 'controllers/PluginController.php';
require_once 'controllers/ApiController.php';

$plugin = new plugin();


add_action("plugins_loaded", function () use ($plugin) {
    Hooks::add_hooks($plugin->config);
});
<?php
/*
Plugin Name: Custom IU Forms
Description: A custom plugin that adds a sidebar menu item for displaying published forms.
Version: 1.0
Author: Silvera Enterprises
*/


require_once 'autoload.php';

$plugin = new PluginController();


HooksController::load();


$api = new ApiController("ui/v1");
$api->enable();
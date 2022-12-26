<?php

require_once plugin_dir_path(__FILE__) . '/RestController.php';


$api =  new RestController("uiplugin/v1");

$api->create_routes_for_table("forms", $route = "forms");
$api->create_routes_for_table("clients", $route = "clients");
$api->create_routes_for_table("forms_submits", $route = "forms_submits");
<?php

include_once plugin_dir_path(__FILE__) . "controllers/RestController.php";

$api = new RestController("api/uiplugin/v1");

$api->create_routes_for_table("_iu_forms", $route = "forms", ["get", "post", "put", "delete"]);
$api->create_routes_for_table("_iu_clients", $route = "clients", ["get", "post", "put", "delete"]);
$api->create_routes_for_table("_iu_forms_submits", $route = "forms_submits", ["get", "post", "put", "delete"]);
<?php

include_once plugin_dir_path(__FILE__) . "controllers/RestController.php";

$api = new RestController("api/uiplugin/v1");




//GET
$api->register_api_route("forms", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_forms");
    }
));
$api->register_api_route("forms/(?P<id>\d+)", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_forms", $request->get_param("id"));
    }
));
$api->register_api_route("clients", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_clients");
    }
));
$api->register_api_route("clients/(?P<id>\d+)", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_clients", $request->get_param("id"));
    }
));
$api->register_api_route("forms_submits", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_forms_submits");
    }
));
$api->register_api_route("forms_submits/(?P<id>\d+)", array(
    'methods' => 'GET',
    'callback' => function ($request) {
        return RestController::get("_iu_forms_submits", $request->get_param("id"));
    }
));
//POST
$api->register_api_route("forms", array(
    'methods' => 'POST',
    'callback' => function ($request) {
        //eviar datos post
        return RestController::set("_iu_forms", $request->get_body_params());
    }
));
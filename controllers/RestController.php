<?php
include_once plugin_dir_path(__FILE__) . 'DatabaseController.php';

class RestController extends DatabaseController
{
    public function __construct($fragments_base)
    {
        $this->base = $fragments_base;
        $this->config = plugin::getConfig();
    }
    public function register_api_route($route, $params)
    {
        $register = function () use ($route, $params) {
            register_rest_route($this->base, '/' . $route, $params);
        };
        $register->bindTo($this, $this);
        add_action('rest_api_init', $register);
    }
    public function create_group_getter_routes($table, $route)
    {
        $table = $this->config["meta_key"] . "_" . $table;
        $this->register_api_route($route, array(
            'methods' => 'GET',
            'callback' => function ($request) use ($table) {
                return $this->get($table);
            }
        ));
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'GET',
            'callback' => function ($request) use ($table) {
                return $this->get($table, $request->get_param("id"));
            }
        ));
    }
    public function create_group_poster_routes($table, $route)
    {
        $table = $this->config["meta_key"] . "_" . $table;
        $this->register_api_route($route, array(
            'methods' => 'POST',
            'callback' => function ($request) use ($table) {
                return $this->set($table, $request->get_body_params());
            }
        ));
    }
    public function create_group_putter_routes($table, $route)
    {
        $table = $this->config["meta_key"] . "_" . $table;
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'PUT',
            'callback' => function ($request) use ($table) {

                return $this->update($table, $request->get_params(), $request->get_param("id"));
            }
        ));
    }
    public function create_group_deleter_routes($table, $route)
    {
        $table = $this->config["meta_key"] . "_" . $table;
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'DELETE',
            'callback' => function ($request) use ($table) {
                return $this->delete($table, $request->get_param("id"));
            }
        ));
    }

    public function create_routes_for_table($table, $route, $methods = ["GET", "POST", "PUT", "DELETE"])
    {

        $methods = array_map(function ($method) {
            return strtoupper($method);
        }, $methods);
        if (in_array("GET", $methods)) {
            $this->create_group_getter_routes($table, $route);
        }
        if (in_array("POST", $methods)) {
            $this->create_group_poster_routes($table, $route);
        }
        if (in_array("PUT", $methods)) {
            $this->create_group_putter_routes($table, $route);
        }
        if (in_array("DELETE", $methods)) {
            $this->create_group_deleter_routes($table, $route);
        }
    }
}
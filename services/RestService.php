<?php

class RestService extends DatabaseController
{
    public function __construct($fragments_base)
    {
        $this->base = $fragments_base;
    }
    public function register_api_route($route, $params, $name_route = null)
    {
        if ($name_route != null) {
            MetaService::local_set("api_$name_route",  "/" . $route);
        }

        $register = function () use ($route, $params) {
            register_rest_route($this->base, '/' . $route, $params);
        };
        $register->bindTo($this, $this);
        add_action('rest_api_init', $register);
    }
    public function create_group_getter_routes($table, $route)
    {
        $table =  $table;
        $this->register_api_route($route, array(
            'methods' => 'GET',
            'callback' => function ($request) use ($table) {
                return $this->get($table);
            }
        ), "get_$table");
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'GET',
            'callback' => function ($request) use ($table) {

                return $this->get($table, $request->get_param("id"));
            }
        ), "get_$table" . "_by_id");

        //getWhere
        $this->register_api_route($route . "/where", array(
            'methods' => 'GET',
            'callback' => function ($request) use ($table) {
                //crear el where con los parametros
                $params = $request->get_params();
                $conditions = "";
                foreach ($params as $key => $value) {
                    $conditions .= "$key = $value AND ";
                }
                $conditions = substr($conditions, 0, -4);
                return $this->get($table, array("where" => $conditions));
            }
        ), "get_$table" . "_where");
    }
    public function create_group_poster_routes($table, $route)
    {
        $table = $table;
        $this->register_api_route($route, array(
            'methods' => 'POST',
            'callback' => function ($request) use ($table) {
                $json = json_decode($request->get_body(), true);
                $multiple = $request->get_param("multiple");
                return $this->set($table, $json, $multiple);
            },
            "headers" => array(
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            )
        ), "set_$table");
    }
    public function create_group_putter_routes($table, $route)
    {
        $table =  $table;
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'PUT',
            'callback' => function ($request) use ($table) {

                return $this->update($table, $request->get_params(), $request->get_param("id"));
            }
        ), "update_$table");
    }
    public function create_group_deleter_routes($table, $route)
    {
        $table =  $table;
        $this->register_api_route($route . "/(?P<id>\d+)", array(
            'methods' => 'DELETE',
            'callback' => function ($request) use ($table) {

                return $this->delete($table, $request->get_param("id"));
            }
        ), "delete_$table");
    }

    public function create_routes_for_table($table, $route = null, $methods = ["GET", "POST", "PUT", "DELETE"])
    {
        if ($route == null) {
            $route = $table;
        }

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

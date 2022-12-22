<?php

class RestController extends DatabaseController
{
    public function __construct($fragments_base)
    {
        $this->base = $fragments_base;
    }
    public function register_api_route($route, $params)
    {
        $register = function () use ($route, $params) {
            register_rest_route($this->base, '/' . $route, $params);
        };
        $register->bindTo($this, $this);
        add_action('rest_api_init', $register);
    }
}
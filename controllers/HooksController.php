<?php

class HooksController
{

    private static function add_hooks_plugin_load($config)
    {
        add_filter('page_template', array("PostCallbacks", 'filter_force_template'));
        add_action('admin_menu', array("MenusCallbacks", 'action_create_menu'));
        add_action('admin_enqueue_scripts', function () use ($config) {
            ResourcesCallbacks::add_enqueue_scripts($config);
        });
        add_action('admin_enqueue_scripts', function () use ($config) {
            ResourcesCallbacks::add_enqueue_styles($config);
        });
    }
    public static function remove_hooks($config)
    {
        remove_filter('page_template', array("PostCallbacks", 'filter_force_template'));
        remove_action('admin_menu', array("MenusCallbacks", 'action_create_menu'));
        ResourcesCallbacks::remove_enqueue_scripts($config);
        ResourcesCallbacks::remove_enqueue_styles($config);
        remove_action('wp_enqueue_scripts', array("ResourcesCallbacks", 'add_enqueue_scripts'));
        remove_action('wp_enqueue_styles', array("ResourcesCallbacks", 'add_enqueue_styles'));
        remove_action('send_headers', array("MiddlewareController", 'before'));
        remove_action('wp_footer', array("MiddlewareController", 'after'));
        remove_action('admin_footer', array("MiddlewareController", 'after'));
    }
    public static function load()
    {
        add_action("plugins_loaded", function () {
            self::add_hooks_plugin_load(PluginController::getConfig());
        });
    }
}
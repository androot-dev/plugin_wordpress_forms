<?php

class Hooks
{
    public static function add_hooks($config)
    {
        add_filter('page_template', array("postController", 'filter_force_template'));
        add_action('admin_menu', array("menusController", 'action_create_menu'));
        add_action('admin_enqueue_scripts', function () use ($config) {
            resourcesController::add_enqueue_scripts($config);
        });
        add_action('admin_enqueue_scripts', function () use ($config) {
            resourcesController::add_enqueue_styles($config);
        });
    }
    public static function remove_hooks($config)
    {
        remove_filter('page_template', array("postController", 'filter_force_template'));
        remove_action('admin_menu', array("menusController", 'action_create_menu'));
        error_log("remove_action");
        resourcesController::remove_enqueue_scripts($config);
        resourcesController::remove_enqueue_styles($config);
        remove_action('wp_enqueue_scripts', array("resourcesController", 'add_enqueue_scripts'));
        remove_action('wp_enqueue_styles', array("resourcesController", 'add_enqueue_styles'));
    }
}
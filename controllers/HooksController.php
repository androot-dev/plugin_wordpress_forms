<?php

class Hooks
{
    public static function add_hooks()
    {
        add_filter('page_template', array("postController", 'filter_force_template'));
        add_action('admin_menu', array("menusController", 'action_create_menu'));
    }
    public static function remove_hooks()
    {
        remove_filter('page_template', array("postController", 'filter_force_template'));
        remove_action('admin_menu', array("menusController", 'action_create_menu'));
    }
}
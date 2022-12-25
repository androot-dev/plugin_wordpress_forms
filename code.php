<?php
/*
Plugin Name: Custom IU Forms
Description: A custom plugin that adds a sidebar menu item for displaying published forms.
Version: 1.0
Author: Silvera Enterprises
*/

require_once plugin_dir_path(__FILE__) . 'controllers/CallbacksController.php';
require_once plugin_dir_path(__FILE__) . 'controllers/HooksController.php';
require_once plugin_dir_path(__FILE__) . 'controllers/PluginController.php';


$plugin = new plugin();

add_action("plugins_loaded", function () {
    Hooks::add_hooks();
});

/*



add_action('admin_menu', 'iu_forms_submenu_init');



function custom_forms_enqueue_specific_admin_scripts()
{
    $config = getConfig();
    $screen = get_current_screen();

    if ($config["pages"]) {
        foreach ($config["pages"] as $page) {

            $scripts = isset($page["scripts"]) ? $page["scripts"] : [];
            $key_page = array_search($page, $config["pages"]);

            if (!empty($scripts)) {
                $parent_base = "";
                if (is_numeric($page["location"])) {
                    $parent_base = "toplevel_page_";
                } else {
                    $parent_base = $page["location"]["base"];
                }
                $id_sin_parent = str_replace($parent_base, "", $screen->id);
                if (count($scripts) > 0 && $id_sin_parent === $page["slug"]) {
                    foreach ($scripts as $script) {
                        $key = array_search($script, $scripts);
                        wp_enqueue_script('custom-se' . $key_page . '-script-' . $key, plugin_dir_url(__FILE__) . $script, array(), '1.0.0', true);
                    }
                }
            }
        }
    }
}
add_action('admin_enqueue_scripts', 'custom_forms_enqueue_specific_admin_scripts');

function custom_forms_enqueue_specific_admin_styles()
{
    $config = getConfig();
    $screen = get_current_screen();
    if ($config["pages"]) {
        foreach ($config["pages"] as $page) {
            $styles = isset($page["styles"]) ? $page["styles"] : [];
            $key_page = array_search($page, $config["pages"]);

            if (!empty($styles)) {
                $parent_base = "";
                if (is_numeric($page["location"])) {
                    $parent_base = "toplevel_page_";
                } else {
                    $parent_base = $page["location"]["base"];
                }
                $id_sin_parent = str_replace($parent_base, "", $screen->id);

                if (count($styles) > 0 && $id_sin_parent === $page["slug"]) {

                    foreach ($styles as $style) {
                        $key = array_search($style, $styles);
                        wp_enqueue_style('custom-se' . $key_page . '-style-' . $key, plugin_dir_url(__FILE__) . $style, array(), '1.0.0', 'all');
                    }
                }
            }
        }
    }
    $scripts = isset($config["scripts_global_admin"]) ? $config["scripts_global_admin"] : [];
    if (!empty($scripts)) {
        foreach ($scripts as $script) {
            $key = array_search($script, $scripts);
            if ($script) {
                wp_enqueue_script('iu-forms-' . $key, plugin_dir_url(__FILE__) . $script, array(), '1.0.0', true);
            }
        }
    }
}
add_action('admin_enqueue_scripts', 'custom_forms_enqueue_specific_admin_styles');

// Incluye el archivo 'wp-admin/includes/upgrade.php' para poder usar la función 'dbDelta'
require_once ABSPATH . 'wp-admin/includes/upgrade.php';


// Crea la función que se encargará de crear las tablas


if (count(getDB("tables")) > 0) {
    $src = plugin_dir_path(__FILE__) . "controllers/DatabaseController.php";
    include_once $src;
    $api_enabled = isset($db["enable_api"]) ? true : false;
    if ($api_enabled) {
        include_once plugin_dir_path(__FILE__) . "API.php";
    }
}


function request_api($methos, $url, $headers = null, $body = null)
{

    $response = wp_remote_request($url, array(
        'method' => 'GET',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $headers,
        'body' => $body,
        'cookies' => array()
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    } else {
        return $response;
    }
}

//quitar acciones al desactivar el plugin
function my_plugin_deactivate()
{
    //borar todos los post de cualquier post_type que tenga el meta_key 'iu_forms'
    $args = array(
        'post_type' => 'any',
        'meta_query' => array(
            array(
                'key' => 'iu_forms',
                'value' => true,
                'compare' => '=',
            )
        )
    );
    $posts = get_posts($args);

    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
}
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');
//crear una clase que maneje todo el codigo anterior


*/
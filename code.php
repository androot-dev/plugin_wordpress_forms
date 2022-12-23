<?php
/*
Plugin Name: Custom IU Forms
Description: A custom plugin that adds a sidebar menu item for displaying published forms.
Version: 1.0
Author: Silvera Enterprises
*/


/*
    config.json
    plugin_name - indica el nombre de la carpeta del plugin
    pages - array de paginas
        title - titulo de la pagina
        menu - titulo del menu
        permission - permisos de la pagina
        slug - slug de la pagina
        page - array de paginas
            src - ruta de la pagina
        scripts - array de scripts
            src - ruta del script
        styles - array de estilos
            src - ruta del estilo
        location - ubicacion del menu | si es un numero es un menu principal | si es un objeto es un submenu
            parent - slug del menu padre
            position - posicion del menu
            base - base del menu sin el slug
        icon - icono del menu
        option_page - si es una pagina de opciones  | establecer en true para que sea una pagina de opciones(parece ser lo mismo que agregar un submenu  a options-general.php)

    db.json
    establece la estructura de las tablas de la base de datos para este plugin en la propiedad table y en la 
    propiedad foreign_keys las llaves foraneas
*/
error_log("______________________________________PLUGIN LOADED________________________________________");
error_log("______________________________________PLUGIN LOADED________________________________________");
error_log("______________________________________PLUGIN LOADED________________________________________");
error_log("______________________________________PLUGIN LOADED________________________________________");
require_once plugin_dir_path(__FILE__) . 'controllers/CallbacksController.php';
require_once plugin_dir_path(__FILE__) . 'controllers/PluginController.php';
error_log("____________________________________PluginController________________________________________");
error_log("____________________________________PluginController________________________________________");
error_log("____________________________________PluginController________________________________________");
error_log("____________________________________PluginController________________________________________");

new plugin();
/*

$config = [];
$db = [];

function getConfig($param = null)
{
    global $config;
    if (empty($config)) {
        $config = file_get_contents(plugin_dir_path(__FILE__) . 'config.json');
        $config = json_decode($config, true);
        $config["root"] =  WP_PLUGIN_URL . '/' . $config['plugin_name'] . '/';
    }
    return $param ? $config[$param] : $config;
}
function getDB($param = null)
{
    global $db;
    if (empty($db)) {
        $db = file_get_contents(plugin_dir_path(__FILE__) . 'db.json');
        $db = json_decode($db, true);
    }
    return $param ? $db[$param] : $db;
}
function my_plugin_activate()
{
    // Create a new page
    $config = getConfig();
    $posts = $config["posts"];

    foreach ($posts as $post) {
        $post_content = isset($post["post_content"]) ? plugin_dir_path(__FILE__) . $post["post_content"] : "";
        if ($post_content) {
            $post["post_content"] = file_get_contents($post_content);
        }
        $post_template = isset($post["page_template"]) ? plugin_dir_path(__FILE__) . $post["page_template"] : "";
        if ($post_template) {
            $post["page_template"] = $post_template;
        }
        $post_id = wp_insert_post($post);
        error_log("POST ID: " . $post_id);
        update_post_meta($post_id, "iu_forms", true);
    }
}
$posts = getConfig();
$posts = isset($posts["posts"]) ? $posts["posts"] : [];
if (count($posts) > 0) {

    register_activation_hook(__FILE__, 'my_plugin_activate');
}

function iu_forms_submenu_init()
{
    $pages = getConfig("pages");

    $pages = isset($pages) ? $pages : [];
    foreach ($pages as $page) {

        $location = $page["location"];
        if (is_numeric($location)) {
            if (isset($page["option_page"]) && $page["option_page"]) {
                add_options_page(
                    $page['title'],
                    $page['menu'],
                    $page['permission'],
                    $page['slug'],
                    'iu_forms_submenu_display'
                );
            } else {
                add_menu_page(
                    $page['title'],
                    $page['menu'],
                    $page['permission'],
                    $page['slug'],
                    'iu_forms_submenu_display',
                    $page['icon'],
                    $page['location']
                );
            }
        } else {
            $parent = $page["location"]["parent"];
            $position = $page["location"]["position"] ?? 1;
            add_submenu_page(
                $parent,
                $page['title'],
                $page['menu'],
                $page['permission'],
                $page['slug'],
                'iu_forms_submenu_display',
                $position,
                $page['icon']
            );
        }
    }
}
add_action('admin_menu', 'iu_forms_submenu_init');

function iu_forms_submenu_display()
{
    $pages = getConfig("pages");
    $pages = isset($pages) ? $pages : [];

    if ($pages) {
        foreach ($pages as $key => $value) {
            $page = isset($value["page"]) ? $value["page"] : [];
            $post = isset($value["post"]) ? $value["post"] : false;
            foreach ($page as $key => $src) {
                if ($src) {
                    $path =  plugin_dir_path(__FILE__) . $src;
                    require_once $path;
                }
            }
        }
    }
}

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
function iu_forms_create_tables()
{

    try {
        global $wpdb;
        $json_db = getDB();
        // Obtiene el prefijo de las tablas de la base de datos de WordPress
        $table_prefix = $wpdb->prefix;

        // Crea un array con las sentencias SQL que se usarán para crear las tablas
        $tables_sql = array();

        foreach ($json_db['tables'] as $table_name => $columns) {
            // Construye la sentencia SQL para crear la tabla
            $table_sql = "CREATE TABLE {$table_prefix}{$table_name} (";
            $columns_sql = array();
            $columns_sql[] = "id int(11) NOT NULL AUTO_INCREMENT";
            foreach ($columns as $column_name => $column_definition) {
                $columns_sql[] = "{$column_name} {$column_definition}";
            }
            $columns_sql[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $columns_sql[] = "updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            $table_sql .= implode(', ', $columns_sql);
            $table_sql .= ", PRIMARY KEY (id)";
            $table_sql .= ") DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $tables_sql[] = $table_sql;
        }
        // Ejecuta las sentencias SQL con la función 'dbDelta'
        $res = dbDelta($tables_sql);

        if (isset($json_db['foreigh_keys'])) {
            // Crea un array con las sentencias SQL que se usarán para crear las relaciones entre tablas
            $relationships_sql = array();
            foreach ($json_db['foreigh_keys'] as $table_name => $keys) {
                foreach ($keys as $key_name => $key_definition) {
                    // Construye la sentencia SQL para crear la relación
                    $relationship_sql = "ALTER TABLE {$table_prefix}{$table_name} ADD FOREIGN KEY ({$key_name}) REFERENCES {$table_prefix}{$key_definition['table']}({$key_definition['column']});";
                    $relationships_sql[] = $relationship_sql;
                }
            }
            // Ejecuta las sentencias SQL para crear las relaciones
            foreach ($relationships_sql as $sql) {
                $wpdb->query($sql);
            }
        }
    } catch (Exception $e) {
        error_log("PLUGIN UI FORMS: " . $e->getMessage());
    }
}

if (count(getDB("tables")) > 0) {
    register_activation_hook(__FILE__, 'iu_forms_create_tables');
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
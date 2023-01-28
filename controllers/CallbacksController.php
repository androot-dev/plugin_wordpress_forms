<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

class TablesCallbacks
{
    public static function create_plugin_tables($db, $key)
    {
        global $wpdb;
        //tabla adicional

        if ($db["enable_meta_table"]) {
            $meta_table_plugin = "CREATE TABLE {$wpdb->prefix}{$key}_meta (
                id int(11) NOT NULL AUTO_INCREMENT,
                meta_key varchar(255) NOT NULL UNIQUE,
                meta_value longtext NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $wpdb->query($meta_table_plugin);
        }


        try {
            $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();
            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_name = $key . "_" . $table_name;
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
            error_log(print_r($tables_sql, true));
            $res = dbDelta($tables_sql);

            if (isset($json_db['foreing_keys'])) {
                $relationships_sql = array();
                $count = 0;

                foreach ($json_db['foreing_keys'] as $table_name => $keys) {
                    foreach ($keys as $key_name => $key_definition) {
                        $count++;
                        $constraint_name = "fk_key_{$count}_{$key}_{$table_name}";
                        $table_name2 = $key . "_" . $table_name;
                        $relationship_sql = "ALTER TABLE {$table_prefix}{$table_name2} ADD CONSTRAINT {$constraint_name}  FOREIGN KEY ({$key_name}) REFERENCES {$table_prefix}{$key}_{$key_definition['table']}({$key_definition['column']})";
                        $relationships_sql[] = $relationship_sql;
                    }
                }
                error_log(print_r($relationships_sql, true));
                foreach ($relationships_sql as $sql) {
                    $wpdb->query($sql);
                }
            }

            $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
    public static function delete_plugin_tables($db, $key)
    {
        if ($db["enable_meta_table"]) {
            global $wpdb;
            $table_prefix = $wpdb->prefix;
            $meta_table_plugin = "DROP TABLE IF EXISTS {$table_prefix}{$key}_meta;";
            $wpdb->query($meta_table_plugin);
        }

        try {
            global $wpdb;
            $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();
            if (isset($json_db['foreing_keys'])) {
                $relationships_sql = array();
                $count = 0;
                foreach ($json_db['foreing_keys'] as $table_name => $keys) {
                    $table_name = $key . "_" . $table_name;
                    foreach ($keys as $key_name => $key_definition) {
                        $count++;
                        $constraint_name = "fk_key_{$count}_{$table_name}";
                        $relationship_sql = "ALTER TABLE {$table_prefix}{$table_name} DROP FOREIGN KEY {$constraint_name};";
                        $relationships_sql[] = $relationship_sql;
                    }
                }
                foreach ($relationships_sql as $sql) {
                    $wpdb->query($sql);
                }
            }
            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_name = $key . "_" . $table_name;
                $table_sql = "DROP TABLE IF EXISTS {$table_prefix}{$table_name};";
                $tables_sql[] = $table_sql;
            }
            foreach ($tables_sql as $sql) {
                $wpdb->query($sql);
            }
            $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
    public static function restore_checkpoint($db)
    {
        if ($db["checkpoint"]) {
            //buscar en la carpeta del plugin el archivo checkpoint.sql
            $file = RoutesService::getroot() . "/checkpoint.sql";
            //ejecutar
            if (file_exists($file)) {
                global $wpdb;
                $sql = file_get_contents($file);
                $dbDelta = dbDelta($sql);
                error_log("checkpoint restored");
            }
        }
    }
    public static function is_database_empty($db, $key)
    {
        try {
            global $wpdb;
            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();
            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_sql = "SELECT * FROM {$table_prefix}{$key}_{$table_name};";
                $tables_sql[] = $table_sql;
            }
            foreach ($tables_sql as $sql) {
                $res = $wpdb->get_results($sql);
                if (count($res) > 0) {
                    error_log("not empty the table {$table_name}");
                    return false;
                }
            }
            error_log("empty database");
            return true;
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
    public static function getStatus($db, $key)
    {
        //verificar si todas las tablas estan vacias si es asi devolver false
        try {
            global $wpdb;
            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();
            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_name = $key . "_" . $table_name;
                $table_sql = "SELECT * FROM {$table_prefix}{$table_name};";
                $tables_sql[] = $table_sql;
            }
            foreach ($tables_sql as $sql) {
                $res = $wpdb->get_results($sql);
                if (count($res) > 0) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
    public static function create_backup_data($db, $key)
    {
        //retornar un array con las sentencias INSERT Para restaurar los datos
        $data = [];

        try {
            global $wpdb;

            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();

            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_name = $key . "_" . $table_name;

                $table_sql = "SELECT * FROM {$table_prefix}{$table_name};";

                $tables_sql[$table_prefix . "" . $table_name] = $table_sql;
            }
            foreach ($tables_sql as $key => $sql) {
                $res = $wpdb->get_results($sql);
                foreach ($res as $row) {
                    $columns = array();
                    $values = array();
                    foreach ($row as $column => $value) {
                        $columns[] = $column;
                        $values[] = $value;
                    }
                    $columns = implode(', ', $columns);
                    $values = implode("', '", $values);
                    $data[] = "INSERT INTO {$key} ({$columns}) VALUES ('{$values}');";
                }
            }
            return $data;
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
    public static function restore_backup_data($data)
    {
        //data es un array con las sentencias INSERT
        global $wpdb;
        try {
            $wpdb->query("START TRANSACTION;");
            $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
            $wpdb->query("SET UNIQUE_CHECKS=0;");
            foreach ($data as $sql) {
                $wpdb->query($sql);
            }
            $wpdb->query("SET UNIQUE_CHECKS=1;");
            $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
            $wpdb->query("COMMIT;");
        } catch (Exception $e) {
            $wpdb->query("ROLLBACK;");
            $wpdb->query("SET UNIQUE_CHECKS=1;");
            $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
            error_log($this->name . ":" . $e->getMessage());
        }
    }
}
class PostCallbacks
{
    public static function create_post_type($name, $config = "private", $options = [])
    {
        $labels = [];
        $labels['name'] = _x($name, 'post type general name');
        $labels['singular_name'] = _x($name, 'post type singular name');
        $labels['add_new'] = _x('Add New', $name);
        $labels['add_new_item'] = __('Add New ' . $name);
        $labels['edit_item'] = __('Edit ' . $name);
        $labels['new_item'] = __('New ' . $name);
        $labels['all_items'] = __('All ' . $name);
        $labels['view_item'] = __('View ' . $name);
        $labels['search_items'] = __('Search ' . $name);
        $labels['not_found'] = __('No ' . $name . ' found');
        $labels['not_found_in_trash'] = __('No ' . $name . ' found in the Trash');
        $labels['parent_item_colon'] = '';
        $labels['menu_name'] = $name;

        switch ($config) {
            case 'private':
                $args = array(
                    'labels' => $labels,
                    'hierarchical' => false, // No permite jerarquía en el post_type
                    'description' => "Post type for {$name}", // Descripción del post_type
                    // 'supports' => array( 'title', 'editor', 'custom-fields' ), //es
                    'public' => false, // Establece el post_type como privado
                    'show_ui' => false, // No muestra la interfaz de usuario para este post_type
                    'show_in_menu' => false, // No muestra el post_type en el menú de WordPress
                    'show_in_nav_menus' => false, // No muestra el post_type en los menús de navegación
                    'exclude_from_search' => true, // Excluye el post_type de los resultados de búsqueda
                    'publicly_queryable' => false, // No permite hacer consultas públicas al post_type
                    'query_var' => false, // Permite hacer consultas al post_type
                    'can_export' => false, // Permite exportar el post_type
                    'rewrite' => true, // No permite modificar la URL del post_type
                    "rewrite_slug" => $name, // Permite modificar el slug del post_type
                    'capability_type' => '_plug_admin', // Permite establecer el tipo de permisos para el post_type
                );
                break;
            case 'public':
                $args = array(
                    'labels' => $labels,
                    'hierarchical' => false, // No permite jerarquía en el post_type
                    'description' => "Post type for {$name}", // Descripción del post_type
                    // 'supports' => array( 'title', 'editor', 'custom-fields' ), //es
                    'public' => true, // Establece el post_type como privado
                    'show_ui' => true, // No muestra la interfaz de usuario para este post_type
                    'show_in_menu' => true, // No muestra el post_type en el menú de WordPress
                    'show_in_nav_menus' => true, // No muestra el post_type en los menús de navegación
                    'exclude_from_search' => false, // Excluye el post_type de los resultados de búsqueda
                    'publicly_queryable' => true, // No permite hacer consultas públicas al post_type
                    'query_var' => true, // Permite hacer consultas al post_type
                    'can_export' => true, // Permite exportar el post_type
                    'rewrite' => true, // No permite modificar la URL del post_type
                    "rewrite_slug" => $name, // Permite modificar el slug del post_type
                    'capability_type' => 'post', // Permite establecer el tipo de permisos para el post_type
                );
                break;
        }
        $args = array_merge($args, $options);

        register_post_type($name, $args);
    }
    public static function delete_post_type($name)
    {
        unregister_post_type($name);
    }
    public static function filter_force_template($template)
    {
        $meta = PluginController::getConfig();
        $meta = $meta["meta_key"] . "_page_template";
        $post = get_post_meta(get_the_ID(), $meta, true); // devuelve directorio de la plantilla
        if ($post) {
            $post = base64_decode($post);
            if (file_exists($post)) {
                return $post;
            } else {
                return $template;
            }
        }
    }
    public static function create_posts($config)
    {

        $post_types = isset($config["register_post_type"]) ? $config["register_post_type"] : array();
        foreach ($post_types as $post_type) {
            $name = isset($post_type["name"]) ? $post_type["name"] : "";
            $privacy = isset($post_type["privacy"]) ? $post_type["privacy"] : "public";
            $options = isset($post_type["options"]) ? $post_type["options"] : array();
            if ($name) {
                self::create_post_type($name, $privacy, $options);
            }
        }
        $posts = isset($config["posts"]) ? $config["posts"] : array();
        foreach ($posts as $post) {
            $meta = isset($post["meta"]) ? $post["meta"] : array();
            //retirar el meta del post
            unset($post["meta"]);
            //verficar si menu_order es negativo y si es asi guardarlo en una variable eliminarlo de post y despues que se cree el post actualizarlo
            $menu_order = isset($post["menu_order"]) && $post["menu_order"] < 0 ? false : true;
            if (!$menu_order) {
                $menu_order = $post["menu_order"];
                unset($post["menu_order"]);
            }
            $post_content = isset($post["post_content"]) ? RoutesService::getroot() . $post["post_content"] : "";

            if ($post_content) {
                $post["post_content"] = $post_content;
            }

            $post_id = wp_insert_post($post);
            update_post_meta($post_id, $config["meta_key"], true);
            if (!$menu_order) {
                update_post_meta($post_id, "menu_order", $menu_order);
            }
            foreach ($meta as $key => $value) {
                $post_template = isset($value) ? RoutesService::getroot()  . $value : "";

                $post_template = base64_encode($post_template);
                if ($post_template) {
                    $value = $post_template;
                    update_post_meta($post_id, "{$config["meta_key"]}_page_template", $post_template);
                }
                update_post_meta($post_id, $key, $value);
            }
        }
    }
    public static function delete_posts($config)
    {
        $post_types = isset($config["register_post_type "]) ? $config["register_post_type "] : array();
        foreach ($post_types as $post_type) {
            $name = isset($post_type["name"]) ? $post_type["name"] : "";
            if ($name) {
                self::delete_post_type($name);
            }
        }
        global $wpdb;
        $posts = $wpdb->get_results("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='{$config["meta_key"]}'");
        foreach ($posts as $post) {
            wp_delete_post($post->post_id, true); // true para forzar la eliminación del post y todos sus elementos relacionados
        }

        //borrar los metas
        try {
            $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key='{$config["meta_key"]}'");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
class MenusCallbacks
{

    public static function action_create_menu()
    {
        $config = PluginController::getConfig();
        $pages = $config["pages"];
        $pages = isset($pages) ? $pages : [];
        function display($page, $src)
        {
            $path = plugin_dir_path(__FILE__) . "../" . $src;
            if (file_exists($path)) {

                require_once $path;
            } else {
                status_header(404);
                nocache_headers();
                include(get_query_template('404'));
                exit;
            }
        }
        foreach ($pages as $page) {
            $parent = isset($page["parent"]) ? $page["parent"] : "";
            $src = $page["src"];

            if ($parent) {
                add_submenu_page(
                    $parent,
                    $page["menu"]['title'],
                    $page["menu"]['name'],
                    $page["menu"]['permission'],
                    $page["menu"]['slug'],
                    function ($page) use ($src) {
                        display($page, $src);
                    },
                    $page["menu"]["location"],
                    $page["menu"]['icon']
                );
            } else {
                add_menu_page(
                    $page["menu"]['title'],
                    $page["menu"]['name'],
                    $page["menu"]['permission'],
                    $page["menu"]['slug'],
                    function ($page) use ($src) {
                        display($page, $src);
                    },
                    $page["menu"]['icon'],
                    $page["menu"]['location']
                );
            }
        }
    }
}
class ResourcesCallbacks
{

    public static function add_enqueue_scripts($config)
    {
        $screen = get_current_screen();
        $slug = $screen->base;
        $tag = $config["meta_key"];
        $loads = 0;
        $packs = isset($config["resources"]["packs"]) ? $config["resources"]["packs"] : array();

        if (isset($config["resources"]["js"])) {
            $js = $config["resources"]["js"];
            $count = 0;
            foreach ($js as $key => $value) {
                $count++;
                if (is_array($value)) {
                    if (count($packs) > 0) {
                        $list = array_filter($value, function ($item) {
                            return explode(".", $item)[0] == "packs";
                        });
                        if (count($list) > 0) {
                            $list = array_map(function ($item) {
                                return explode(".", $item);
                            }, $list);
                            foreach ($list as $item) {
                                $name_pack = $item[1];
                                $url =  RoutesService::getresource($key);

                                if (in_array("admin", $packs[$name_pack]) && is_admin()) {
                                    wp_enqueue_script("{$tag}_auto_script_admin" . $count, $url, array(), false, true);
                                    $loads++;
                                } else if (in_array($slug, $packs[$name_pack])) {

                                    wp_enqueue_script("{$tag}_auto_script_array" . $count, $url, array(), false, true);
                                    $loads++;
                                }
                            }
                        }
                    }

                    if (in_array($slug, $value)) {
                        $url =  RoutesService::getresource($key);

                        if (in_array("admin", $value) && is_admin()) {
                            wp_enqueue_script("{$tag}_auto_script_admin" . $count, $url, array(), false, true);
                            $loads++;
                        } else if (in_array($slug, $value)) {
                            wp_enqueue_script("{$tag}_auto_script_array" . $count, $url, array(), false, true);
                            $loads++;
                        }
                    }
                }
            }
        }
        error_log("{$loads} scripts loads in {$slug}");
    }
    public static function add_enqueue_styles($config)
    {
        $screen = get_current_screen();
        $slug = $screen->base;
        $tag = $config["meta_key"];
        $packs = isset($config["resources"]["packs"]) ? $config["resources"]["packs"] : array();
        $loads = 0;
        if (isset($config["resources"]["css"])) {
            $css = $config["resources"]["css"];
            $count = 0;
            foreach ($css as $key => $value) {
                $count++;
                if (is_array($value)) {
                    if (count($packs) > 0) {
                        $list = array_filter($value, function ($item) {
                            return explode(".", $item)[0] == "packs";
                        });
                        if (count($list) > 0) {
                            $list = array_map(function ($item) {
                                return explode(".", $item);
                            }, $list);
                            foreach ($list as $item) {
                                $name_pack = $item[1];
                                $url =  RoutesService::getresource($key);
                                if (in_array("admin", $packs[$name_pack]) && is_admin()) {
                                    wp_enqueue_style("{$tag}_auto_style_admin" . $count, $url, array(), false, "all");
                                    $loads++;
                                } else if (in_array($slug, $packs[$name_pack])) {
                                    wp_enqueue_style("{$tag}_auto_style_array" . $count, $url, array(), false, "all");
                                    $loads++;
                                }
                            }
                        }
                    }
                    if (in_array($slug, $value)) {
                        $url =  RoutesService::getresource($key);
                        if (in_array("admin", $value) && is_admin()) {
                            wp_enqueue_style("{$tag}_auto_style_admin" . $count, $url, array(), false, "all");
                            $loads++;
                        } else if (in_array($slug, $value)) {
                            wp_enqueue_style("{$tag}_auto_style_array" . $count, $url, array(), false, "all");
                            $loads++;
                        }
                    }
                }
            }
        }
        error_log("{$loads} styles loads in {$slug}");
    }
    public static function remove_enqueue_scripts($config)
    {
        $screen = get_current_screen();
        $slug = $screen->base;
        $tag = $config["meta_key"];
        if (isset($config["resources"]["js"])) {
            $js = $config["resources"]["js"];
            $count = 0;
            foreach ($js as $key => $value) {
                $count++;
                if (is_array($value)) {
                    if (in_array($slug, $value)) {
                        wp_dequeue_script("{$tag}_auto_script_array" . $count);
                    } else if (in_array("admin", $value) && is_admin()) {
                        wp_dequeue_script("{$tag}_auto_script_admin" . $count);
                    }
                }
            }
        }
    }
    public static function remove_enqueue_styles($config)
    {
        $screen = get_current_screen();
        $slug = $screen->base;
        $tag = $config["meta_key"];
        if (isset($config["resources"]["css"])) {
            $css = $config["resources"]["css"];
            $count = 0;
            foreach ($css as $key => $value) {
                $count++;
                if (is_array($value)) {
                    if (in_array($slug, $value)) {
                        wp_dequeue_style("{$tag}_auto_style_array" . $count);
                    } else if (in_array("admin", $value) && is_admin()) {
                        wp_dequeue_style("{$tag}_auto_style_admin" . $count);
                    }
                }
            }
        }
    }
    public static function add_cors_http_header()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
}
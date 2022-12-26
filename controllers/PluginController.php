<?php

class plugin
{
    public function __construct()
    {
        $config = wp_normalize_path($this::root() . 'config.json');
        $db = wp_normalize_path($this::root() . 'db.json');
        //Gloabl variables
        $this->config = json_decode(file_get_contents($config), true);
        $this->db = json_decode(file_get_contents($db), true);
        $this->name = $this->config['plugin_folder_name'];
        $this->init();
    }
    private function init()
    {
        register_activation_hook($this::root() . 'code.php', array($this, 'install'));
        register_deactivation_hook($this::root() . 'code.php', array($this, 'desactive'));
        register_uninstall_hook($this::root() . 'code.php', array("self", 'uninstall'));
    }

    public static function root()
    {
        return plugin_dir_path(dirname(__FILE__));
    }
    public static function getConfig()
    {
        $config = wp_normalize_path(self::root() . 'config.json');
        return json_decode(file_get_contents($config), true);
    }
    public static function getDb()
    {
        $db = wp_normalize_path(self::root() . 'db.json');
        return json_decode(file_get_contents($db), true);
    }
    public function install()
    {
        if (isset($this->db['tables']) && !empty($this->db['tables'])) {
            tablesController::create_plugin_tables($this->db, $this->config["meta_key"]);
        }
        if (isset($this->config['posts']) && !empty($this->config['posts'])) {
            postController::create_posts($this->config);
        }
        $this->restore_backup();
    }
    public function desactive()
    {
        if (isset($this->config['posts']) && !empty($this->config['posts'])) {
            postController::delete_posts($this->config);
        }
        if (isset($this->db['tables']) && !empty($this->db['tables'])) {
            $this->backup_data();
            tablesController::delete_plugin_tables($this->db, $this->config["meta_key"]);
        }

        Hooks::remove_hooks($this->config);
    }
    private function backup_data()
    {

        $new_folder = isset($this->db["backup_csv"]) ? $this->db["backup_csv"] : null;
        $status = tablesController::getStatus($this->db, $this->config["meta_key"]);
        if ($status) {
            //dividir el dir en partes 
            $dir = explode("/", $new_folder);
            $dir = array_filter($dir);
            $dir = array_values($dir);
            $dir = implode("/", $dir);
            $dir = wp_normalize_path("wp-content/" . $dir);
            $dir = wp_normalize_path(ABSPATH . $dir);
            wp_mkdir_p($dir);
            $data_for_file_csv = tablesController::create_backup_data($this->db, $this->config["meta_key"]);
            $file = $dir . "/backup_" . $this->name . ".csv";
            $file = wp_normalize_path($file);
            $file = fopen($file, "w");
            foreach ($data_for_file_csv as $line) {
                fwrite($file, $line . "\n");
            }
            fclose($file);
            error_log("created backup");
        } else {
            error_log("no data to backup in database");
        }
    }
    private function restore_backup()
    {
        // clientController::question_alert("¿Desea restaurar los datos de respaldo?", "restore_data");
        //clientController::catch_response("restore_data", function () {
        $new_folder = isset($this->db["backup_csv"]) ? $this->db["backup_csv"] : null;
        //verficiar si existe un archivo de respaldo en la carptea de respaldos
        if (isset($new_folder) && !empty($new_folder)) {
            $dir = explode("/", $new_folder);
            $dir = array_filter($dir);
            $dir = array_values($dir);
            $dir = implode("/", $dir);
            $dir = wp_normalize_path("wp-content/" . $dir);
            $dir = wp_normalize_path(ABSPATH . $dir);
            $file = $dir . "/backup_" . $this->name . ".csv";
            $file = wp_normalize_path($file);
            if (file_exists($file)) {
                $data = file($file);
                $data = array_filter($data);
                $data = array_values($data);
                $data = array_map(function ($item) {
                    return $item;
                }, $data);
                tablesController::restore_backup_data($data);
            }
        }
        //});
    }
    public function uninstall()
    {
        $this->desactive();
    }
}
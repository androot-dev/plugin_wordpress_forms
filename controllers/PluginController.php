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
    }
    public function desactive()
    {
        if (isset($this->config['posts']) && !empty($this->config['posts'])) {
            postController::delete_posts($this->config);
        }
        Hooks::remove_hooks();
        $this->backup_data();
    }
    private function backup_data()
    {
        if (isset($this->db['tables']) && !empty($this->db['tables'])) {
            tablesController::delete_plugin_tables($this->db, $this->config["meta_key"]);
        }
    }
    public function uninstall()
    {
        $this->desactive();
    }
}
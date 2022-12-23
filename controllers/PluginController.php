<?php
class plugin extends callbacks
{
    public function __construct()
    {
        $config = wp_normalize_path($this::root() . 'config.json');
        $this->config = json_decode(file_get_contents($config), true);
        $db = wp_normalize_path($this::root() . 'db.json');
        $this->db = json_decode(file_get_contents($db), true);
        error_log(print_r($this->config, true));
        $this->name = $this->config['plugin_folder_name'];
        $this->init();
    }
    public static function  root()
    {
        return plugin_dir_url(__FILE__);
    }
    public function init()
    {
        register_activation_hook(__FILE__, array($this, 'install'));
    }
    public function install()
    {
        $this->create_plugin_tables($this->db);
    }
}
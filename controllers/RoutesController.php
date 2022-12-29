<?php

class RoutesController
{
    public static function  getroot($param = "relative")
    {
        if ($param == "relative") {
            return plugin_dir_path(dirname(__FILE__));
        } else {
            return plugin_dir_url(dirname(__FILE__));
        }
    }

    public static function getConfig()
    {
        $config = wp_normalize_path(self::getroot() . 'config.json');
        return json_decode(file_get_contents($config), true);
    }
    public static function getresource($src)
    {
        $config = self::getConfig()["plugin_folder_name"];
        return "/wp-content/plugins/" . $config . "/resources/" . $src;
    }
    public static function getview($src)
    {
        $config = self::getConfig()["plugin_folder_name"];
        return "/wp-content/plugins/" . $config . "/views/" . $src;
    }
    public static function gettemplate($src)
    {
        $config = self::getConfig()["plugin_folder_name"];
        return "/wp-content/plugins/" . $config . "/views/templates/" . $src;
    }
}
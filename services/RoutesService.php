<?php

class RoutesService
{
    public static function getroot($param = "relative")
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
    public static function getresource($src, $absolute = false)
    {
        $config = self::getConfig()["plugin_folder_name"];
        if ($absolute) {
            return self::getroot("absolute") . "resources/" . $src;
        }
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
    public static function getupload($src = "", $mod = "normal")
    {
        $config = self::getConfig()["upload_folder"]; //wp-content...
        if ($mod == "url") {
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
            $domainName = $_SERVER['HTTP_HOST'];
            return $protocol . $domainName . $config . "/" . $src;
        } else if ($mod == "absolute") {
            return wp_normalize_path(ABSPATH . $config . "/" . $src);
        } else if ($mod == "normal") {
            return $config . "/" . $src;
        }
    }
    public static function getlistfilesapplication($id_application)
    {
        $config = self::getConfig()["upload_folder"]; //wp-content...
        $url = wp_normalize_path(ABSPATH . $config . "/applications/application_$id_application");
        $files = scandir($url);
        $files = array_diff($files, array('.', '..'));
        //verificar si existen los archivos
        $files = array_values($files);
        $exist = [];
        foreach ($files as $key => $value) {
            if (file_exists($url . "/" . $value)) {
                $url_to_relative = self::getupload("applications/application_$id_application/" . $value, "normal");
                $exist[] = $url_to_relative;
            }
        }
        return $exist;
    }
    public static function getemplateform($src = "", $mod = "normal")
    {
        $config = self::getConfig()["plugin_folder_name"];

        if ($mod == "url") {
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
            $domainName = $_SERVER['HTTP_HOST'];
            return $protocol . $domainName . "/wp-content/plugins/" . $config . "/templates_coords/" . $src;
        } else if ($mod == "absolute") {
            return wp_normalize_path(ABSPATH . "wp-content/plugins/" . $config . "/templates_coords/" . $src);
        } else if ($mod == "normal") {
            return "/wp-content/plugins/" . $config . "/templates_coords/" . $src;
        }
    }
    public static function get_template_part($src, $args = [])
    {
        $config = self::getConfig()["plugin_folder_name"];
        $url = self::getroot() . "views/templates/" . $src;

        if (file_exists($url)) {
            $args = (object) $args;
            include $url;
        } else {
            echo "Template not found";
        }
    }
    public static function get_template_url($name)
    {
        $config = self::getConfig()["plugin_folder_name"];
        return "/wp-content/plugins/" . $config . "/views/templates/" . $name;
    }
    public static function get_api_base()
    {
        error_log(MetaService::local_get("api"));
        $base_url = get_site_url() . "/wp-json/" . MetaService::local_get("api") . "/";
        return $base_url;
    }
    public static function get_api_route($name)
    {

        return self::get_api_base() . MetaService::local_get("api_$name");
    }
}
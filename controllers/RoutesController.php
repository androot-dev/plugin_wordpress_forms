<?php

class routesController
{
    public static function  getroot($param = "relative")
    {
        if ($param == "relative") {
            return plugin_dir_path(dirname(__FILE__));
        } else {
            return plugin_dir_url(dirname(__FILE__));
        }
    }
    public static function getresource($src)
    {
        return self::getroot() . "resources/" . $src;
    }
}
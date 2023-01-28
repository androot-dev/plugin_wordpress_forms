<?php


class MetaService
{
    public static function get($name)
    {

        $data =  DatabaseController::get("meta", [
            "where" => "meta_key = '$name'"
        ]);
        if (count($data) > 0) {
            return $data[0]["meta_value"];
        } else {
            return null;
        }
    }
    public static function set($name, $value)
    {
        $data =  DatabaseController::set("meta", [
            "meta_key" => $name,
            "meta_value" => $value
        ]);

        return $data;
    }
    public static function update($key, $value, $option)
    {
        $data =  DatabaseController::update("meta", [
            "meta_value" => $value

        ], "meta_key = '$key'");
        return $data;
    }
    public static function delete($key)
    {
        $data =  DatabaseController::delete("meta", "meta_key = '$key'");
        return $data;
    }
    public static function local_get($name)
    {
        global $meta_global;

        if (isset($meta_global[$name])) {
            return $meta_global[$name];
        } else {
            return null;
        }
    }
    public static function local_set($name, $value)
    {
        global $meta_global;
        $meta_global[$name] = $value;
    }
    public static function local_update($key, $value)
    {
        global $meta_global;
        $meta_global[$key] = $value;
    }
    public static function local_delete($key)
    {
        global $meta_global;
        unset($meta_global[$key]);
    }
}

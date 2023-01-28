<?php


function view($src, $data = [])
{
    $path = RoutesService::getview($src);
    if (file_exists($path)) {
        extract($data);
        include_once $path;
    }
}
function filterNoPermit($formats)
{
    global $permited;
    if ($permited) {
        //eliminar de $formats los que no esten en $permited
        if (empty($permited) || $formats == "empty") {
            return $formats;
        }
        foreach ($formats as $key => $value) {
            $name = $value["name"];
            //hace rque name sea desde el primer caracter hasta que encuentre |
            $name = substr($name, 0, strpos($name, "|"));
            //uppercase
            $name = strtoupper($name);
            //trim
            $name = trim($name);

            if (!in_array($name, $permited)) {
                unset($formats[$key]);
            }
        }
        return $formats;
    } else {
        return $formats;
    }
}

$meta_global = []; //variables globales
$permited = null;

$config  = file_get_contents(__DIR__ . "/config.json");
$config = json_decode($config, true);
$key_pluging = $config["meta_key"];
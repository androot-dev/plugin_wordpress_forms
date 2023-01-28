<?php

function autoload_classes($class_name)
{



    // Construye la ruta del archivo de la clase
    //verficiar si los ultimos caracteres son Callbacks 
    $last = substr($class_name, -9);
    $class_name = $last == "Callbacks" ? "CallbacksController" : $class_name;
    //verificar si termina en Service
    $is_service = substr($class_name, -7) == "Service" ? true : false;
    $folder = $is_service ? "services" : "controllers";

    $filename = dirname(__FILE__) . '/' . $folder . '/' . str_replace('\\', '/', $class_name) . '.php';
    // Si el archivo existe, lo incluye
    if (file_exists($filename)) {
        require_once $filename;
    }
}

// Registra la función de carga automática
spl_autoload_register('autoload_classes');

include_once "functions.php";
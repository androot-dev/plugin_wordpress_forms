<?php


function view($src, $data = [])
{
    $path = RoutesController::getview($src);
    if (file_exists($path)) {
        extract($data);
        include_once $path;
    }
}
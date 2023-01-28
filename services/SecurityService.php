<?php

class SecurityService
{
    public static function generate_key_url()
    {
        $params = array(
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'random' => rand(0, 1000000)

        );
        $key = md5(implode('', $params));
        return $key;
    }
}
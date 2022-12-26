<?php
/*

class clientController
{
    public static function catch_response($name_callback, $callback)
    {
        add_action("wp_ajax_$name_callback", $callback);
    }
    public static function question_alert($question, $name_callback)
    {
        $param = array(
            "action" => $name_callback
        );
        $url = add_query_arg($param, admin_url('admin-ajax.php'));

        wp_localize_script('ajax_request', 'ajax_object', array(
            'ajax_url' => $url,
            "type"     => "question",
            "message"  => $question,
            "callback" => $name_callback,
        ));
        error_log("prepare_scripts_ajax---" . getRoot() . 'js/ajax_request.js');
        wp_enqueue_script('ajax_request', getRoot() . 'js/ajax_request.js', array('jquery'));
    }

    public static function prepare_scripts_ajax()
    {
        error_log("prepare_scripts_ajax---" . plugin_dir_path(__FILE__) . 'js/ajax_request.js');
        wp_enqueue_script('ajax_request', plugin_dir_path(__FILE__) . 'js/ajax_request.js', array('jquery'));
    }
    public static function delete_scripts_ajax()
    {
        wp_dequeue_script('ajax_request');
    }
} 

*/
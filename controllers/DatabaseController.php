<?php
class DatabaseController
{
    public function linkData($data, $replace, $table_new_value)
    {
        //link("id_form", "forms.id", "name_forms")
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;
        $table = explode(".", $table_new_value)[0];
        $table_name = $prefix . $table;
        $new_value = explode(".", $table_new_value)[1];

        foreach ($data as $key => $value) {
            $id = $value[$replace];
            $data[$key][$table_new_value] = $wpdb->get_results("SELECT $new_value FROM $table_name WHERE id = $id")[0]->$new_value;
        }

        return $data;
        //buscar

    }
    public static function get($table, $options = null)
    {
        global $wpdb;

        $options_default = [
            "order" => null,
            "limit" => null,
            "offset" => 0,
            "where" => null,
            "join" => null,
            "select" => "*",
            "group" => null,
            "having" => null,
            "distinct" => false,
            "debug" => false,
        ];
        if (is_array($options)) {
            $options = array_merge($options_default, $options);
        } else {
            $options_default["where"] = $options;
            $options = $options_default;
        }
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;
        $table_name = $prefix . $table;
        $sql = "SELECT " . ($options["distinct"] ? "DISTINCT " : "") . $options["select"] . " FROM $table_name";
        if ($options["join"]) {
            $sql .= " " . $options["join"];
        }
        if ($options["where"]) {
            $sql .= " WHERE " . $options["where"];
        }
        if ($options["group"]) {
            $sql .= " GROUP BY " . $options["group"];
        }
        if ($options["having"]) {
            $sql .= " HAVING " . $options["having"];
        }
        $sql .= " ORDER BY " . ($options["order"] ? $options["order"] : "id DESC");
        $sql .= " LIMIT " . ($options["limit"] ? $options["limit"] : 1000);
        $sql .= " OFFSET " . $options["offset"];

        if ($options["debug"]) {
            error_log($sql);
        }
        try {
            $result = $wpdb->get_results($sql, ARRAY_A);
            if (empty($result)) {
                return false;
            } else {

                return $result;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }
    public static function set($table, $data)
    {
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;


        $table_name = $prefix . $table;
        $sql = "INSERT INTO $table_name (";
        $columns = "";
        $values = "";
        foreach ($data as $key => $value) {
            $columns .= $key . ",";
            $values .= "'" . $value . "',";
        }
        $columns = substr($columns, 0, -1);
        $values = substr($values, 0, -1);
        $sql .= $columns . ") VALUES (" . $values . ")";
        try {
            $result = $wpdb->query($sql);
            if ($result) {
                $result = $wpdb->insert_id;
            } else {
                $result = false;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }
    public static function update($table, $data, $id)
    {
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;

        $table_name = $prefix . $table;
        $sql = "UPDATE $table_name SET updated_at = CURRENT_TIMESTAMP, ";
        $columns = "";
        foreach ($data as $key => $value) {
            if ($key != "id") {
                $columns .= $key . " = '" . $value . "',";
            }
        }
        $columns = substr($columns, 0, -1);
        $sql .= $columns . " WHERE id = $id";

        try {
            $wpdb->query($sql);
            // Retrieve the updated record
            $sql = "SELECT * FROM $table_name WHERE id = $id";
            $result = $wpdb->get_results($sql, ARRAY_A);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public static function delete($table, $id)
    {
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;
        $table_name = $prefix . $table;
        $sql = "DELETE FROM $table_name WHERE id = $id";
        try {
            $result = $wpdb->query($sql);
            if ($result) {
                $result = true;
            } else {
                $result = false;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }
}
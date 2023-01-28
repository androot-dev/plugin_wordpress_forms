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
        if ($data && $data != "empty") {
            foreach ($data as $key => $value) {
                $id = $value[$replace];
                $new_key = str_replace(".", "_", $table_new_value);
                $value[$new_key] = $wpdb->get_results("SELECT $new_value FROM $table_name WHERE id = $id");
                if ($value[$new_key] && $value[$new_key] != "empty") {
                    $value[$new_key] = $value[$new_key][0]->$new_value;
                } else {
                    $value[$new_key] = "";
                }
            }
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
            "like" => null,
        ];
        if (is_array($options)) {
            $options = array_merge($options_default, $options);
        } else {
            if ($options) {
                $options_default["where"] = "id = $options";
            }
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
        error_log($sql);
        if ($options["where"]) {
            $sql .= " WHERE " . $options["where"];
        }
        if ($options["group"]) {
            $sql .= " GROUP BY " . $options["group"];
        }
        if ($options["having"]) {
            $sql .= " HAVING " . $options["having"];
        }
        if ($options["like"]) {
            $sql .= " LIKE " . $options["like"];
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
                return "empty";
            } else {

                return $result;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }
    public static function set($table, $data, $multiple = false)
    {
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;


        $table_name = $prefix . $table;
        $sql = "INSERT INTO $table_name (";
        $columns = "";
        $values = "";
        if (!$multiple) {
            foreach ($data as $key => $value) {
                $columns .= $key . ",";
                $value = sanitize_text_field($value);
                $value = str_replace("'", "\'", $value);
                $values .= "'" . $value . "',";
            }
            $columns = substr($columns, 0, -1);
            $values = substr($values, 0, -1);


            $sql .= $columns . ") VALUES (" . $values . ")";
        } else {
            $columns = array_keys($data[0]);
            $columns = implode(",", $columns);

            $sql .= $columns . ") VALUES ";
            foreach ($data as $key => $value) {
                $values .= "(";
                foreach ($value as $key2 => $value2) {
                    $value2 = sanitize_text_field($value2);
                    $value2 = str_replace("'", "\'", $value2);
                    $values .= "'" . $value2 . "',";
                }
                $values = substr($values, 0, -1);
                $values .= "),";
            }

            $values = substr($values, 0, -1);
            $sql .= $values;
        }
        try {
            error_log($sql);
            $result = $wpdb->query($sql);
            if ($result) {
                $result = $wpdb->insert_id;
            } else {

                $result = [
                    "error" => true,
                    "message" => "Failed to insert record",
                    "sql" => $sql,
                    "db_error" => $wpdb->last_error,

                ];
                return $result;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $result;
    }
    public static function update($table, $data, $options)
    {
        if (is_int($options) || is_numeric($options)) {
            $options = "id = $options";
        }
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
        $sql .= $columns . " WHERE $options";

        try {
            $wpdb->query($sql);
            // Retrieve the updated record
            $sql = "SELECT * FROM $table_name WHERE $options";
            echo $sql;
            $result = $wpdb->get_results($sql, ARRAY_A);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public static function updateWhere($table, $data, $where)
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
        $sql .= $columns . " WHERE $where";

        try {
            $wpdb->query($sql);
            // Retrieve the updated record
            $sql = "SELECT * FROM $table_name WHERE $where";
            $result = $wpdb->get_results($sql, ARRAY_A);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public static function delete($table, $options)
    {
        if (is_int($options)) {
            $options = "id = $options";
        }
        global $wpdb;
        $plugin_prefix = PluginController::getConfig()["meta_key"];
        $prefix = $wpdb->prefix . $plugin_prefix . "_";
        $wpdb = $wpdb;
        $table_name = $prefix . $table;

        $sql = "DELETE FROM $table_name WHERE $options";
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
<?php
class DatabaseController
{
    //usar para definir consultas que pueden adaptarse a cualquier tabla
    public static function get($table, $id = null)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $wpdb = $wpdb;
        $table_name = $prefix . $table;
        $sql = "SELECT * FROM $table_name";
        if ($id) {

            $sql .= " WHERE id = $id";
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
        $prefix = $wpdb->prefix;
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
        $prefix = $wpdb->prefix;
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
        $prefix = $wpdb->prefix;
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
<?php
class callbacks
{
    public static function create_plugin_tables($db)
    {
        try {
            global $wpdb;
            $json_db = $db;
            $table_prefix = $wpdb->prefix;
            $tables_sql = array();
            foreach ($json_db['tables'] as $table_name => $columns) {
                $table_sql = "CREATE TABLE {$table_prefix}{$table_name} (";
                $columns_sql = array();
                $columns_sql[] = "id int(11) NOT NULL AUTO_INCREMENT";
                foreach ($columns as $column_name => $column_definition) {
                    $columns_sql[] = "{$column_name} {$column_definition}";
                }
                $columns_sql[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                $columns_sql[] = "updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                $table_sql .= implode(', ', $columns_sql);
                $table_sql .= ", PRIMARY KEY (id)";
                $table_sql .= ") DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $tables_sql[] = $table_sql;
            }
            $res = dbDelta($tables_sql);

            if (isset($json_db['foreigh_keys'])) {
                $relationships_sql = array();
                foreach ($json_db['foreigh_keys'] as $table_name => $keys) {
                    foreach ($keys as $key_name => $key_definition) {
                        $relationship_sql = "ALTER TABLE {$table_prefix}{$table_name} ADD FOREIGN KEY ({$key_name}) REFERENCES {$table_prefix}{$key_definition['table']}({$key_definition['column']});";
                        $relationships_sql[] = $relationship_sql;
                    }
                }
                foreach ($relationships_sql as $sql) {
                    $wpdb->query($sql);
                }
            }
        } catch (Exception $e) {
            error_log($this->name . ":" . $e->getMessage());
        }
    }
}
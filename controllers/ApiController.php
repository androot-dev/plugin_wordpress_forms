<?php




class ApiController extends RestController
{

    public function __construct($fragments_base)
    {
        parent::__construct($fragments_base);
    }
    public function autoApi($arrayTables = null, $param = ["get", "post", "put", "delete"])
    {
        if ($arrayTables == null) {
            $tables =  PluginController::getDb()["tables"];
            foreach ($tables as $key => $table) {
                $this->create_routes_for_table($key, null, $param);
            }
        } else {
            foreach ($arrayTables as $table) {
                $this->create_routes_for_table($table, null, $param);
            }
        }
    }
    public function manualApi()
    {
        /* empty */
    }
}
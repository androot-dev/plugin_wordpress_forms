<?php


class ApiController extends RestService
{
    public function __construct($fragments_base)
    {
        $this->base = $fragments_base;
        MetaService::local_set("api", $this->base);
        parent::__construct($fragments_base);
        global $key_pluging;
        $this->key = $key_pluging;
    }
    public function enable($arrayTables = null, $param = ["get", "post", "put", "delete"])
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
        $this->load();
    }
    private function load()
    {
        $this->register_api_route("download/pdf", [
            'methods' => 'GET',
            'callback' => function ($request) {
                $src = $request->get_param("src");
                DownloadService::download_pdf($src);
            }
        ]);
        $this->register_api_route("inmigration_forms/all", array(
            'methods' => 'GET',
            'callback' => function ($request) {

                return InmigrationService::getForms("all");
            }
        ));
        $this->register_api_route("inmigration_forms/sync", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $slug = $request->get_param("slug");
                return InmigrationService::syncForms($slug);
            }
        ));
        //generacion de key seguras para url
        $this->register_api_route("generate_key_url", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return SecurityService::generate_key_url();
            }
        ));
        $this->register_api_route("set_pdf_annotations", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $data =  $request->get_body();
                return PdfService::fillForm($data);
            }
        ));
        $this->register_api_route("getApplications", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $data = DatabaseController::get("applications", [
                    "join" => "JOIN wp_$this->key" . "_clients ON wp_$this->key" . "_clients.id = wp_$this->key" . "_applications.id_client",
                    "order" => "wp_$this->key" . "_applications.id DESC",
                ]);
                return $data;
            }
        ));
        $this->register_api_route("getQuestionsByNameGroup/(?P<name>\w+)", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $name = $request->get_param("name");

                $data = DatabaseController::get("questions_groups", [
                    "select " => "questions_ids",
                    "where" => " name = '$name'"
                ]);
                if ($data && $data != "empty") {
                    $questions_ids = $data[0]["questions_ids"];
                    $questions_ids = explode(",", $questions_ids);
                    $questions_ids = implode("','", $questions_ids);
                    $questions = DatabaseController::get("questions", [
                        "where" => " id IN ('$questions_ids')"
                    ]);
                    return $questions;
                } else {
                    return [];
                }


                return $data;
            }
        ));
        $this->register_api_route("getQuestionsGroupsByIdFile/(?P<id>\w+)", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $id = $request->get_param("id");
                $data = DatabaseController::get("questions_groups_link_online_files", [
                    "select " => "groups_ids",
                    "where" => "id_file = '$id'"
                ]);

                if ($data && $data != "empty") {
                    $groups_ids = $data[0]["groups_ids"];
                    $groups_ids = explode(",", $groups_ids);
                    $groups_ids = implode("','", $groups_ids);
                    $group = DatabaseController::get("questions_groups", [
                        "where" => " id IN ('$groups_ids')"
                    ]);
                    return $group;
                } else {
                    return [];
                }
                return $data;
            }
        ));
        $this->register_api_route("getOptionsByQuestionId/(?P<id>\w+)", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $id = $request->get_param("id");
                $data = DatabaseController::get("questions", [
                    "where" => " id = '$id'"
                ]);
                if ($data && $data != "empty") {

                    return $data;
                } else {
                    return [];
                }
            }
        ));

        $this->register_api_route("getApplicationFilestByIdApplication/(?P<id>\d+)", array(
            'methods' => 'GET',
            'callback' => function ($request) {
                $data = RoutesService::getlistfilesapplication($request->get_param("id"));
                return $data;
            }
        ));
        $this->register_api_route("setQuestionInGroup", array(
            'methods' => 'POST',
            'callback' => function ($request) {
                try {
                    $name = $request->get_param("group");

                    $id_question = $request->get_param("id_question");
                    $group = DatabaseController::get("questions_groups", [
                        "where" => "name = '$name'"
                    ]);

                    if ($group && $group != "empty") {
                        $questions_ids = $group[0]["questions_ids"];
                        $questions_ids = explode(",", $questions_ids);
                        $questions_ids[] = $id_question;
                        $questions_ids = implode(",", $questions_ids);
                        $res = DatabaseController::updateWhere(
                            "questions_groups",
                            [
                                "questions_ids" => $questions_ids
                            ],
                            "name = '$name'"
                        );



                        return $res;
                    } else {
                        $res = DatabaseController::set("questions_groups", [
                            "name" => $name,
                            "questions_ids" => $id_question
                        ]);
                        return $res;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        ));
        $this->register_api_route("setGroupInOnlineFile", array(
            'methods' => 'POST',
            'callback' => function ($request) {
                try {

                    $groups = $request->get_param("groups");
                    $id_file = $request->get_param("id_file");

                    $group = DatabaseController::get("questions_groups_link_online_files", [
                        "where" => "id_file = '$id_file'"
                    ]);

                    if ($group && $group != "empty") {
                        if ($groups == "" || $groups == null || $groups == []) {
                            $res = DatabaseController::delete(
                                "questions_groups_link_online_files",
                                "id_file = '$id_file'"
                            );
                        } else {
                            $res = DatabaseController::updateWhere(
                                "questions_groups_link_online_files",
                                [
                                    "groups_ids" => implode(",", $groups)
                                ],
                                "id_file = '$id_file'"
                            );
                        }
                        return $res;
                    } else {
                        if ($groups == "" || $groups == null || $groups == []) {
                            $res = DatabaseController::delete(
                                "questions_groups_link_online_files",
                                "id_file = '$id_file'"
                            );
                        } else {
                            $res = DatabaseController::set("questions_groups_link_online_files", [
                                "id_file" => $id_file,
                                "groups_ids" => implode(",", $groups)
                            ]);
                        }
                        return $res;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        ));

        $this->register_api_route("createApplication", array(
            'methods' => 'POST',
            'callback' => function ($request) {

                $body = $request->get_body();
                $body = json_decode($body, true);
                $data_applications_files_creation = [];
                try {
                    $id = DatabaseController::set("applications", [
                        "id_client" => $body["client"],
                        "status" => "To Do",
                        "keyform" => SecurityService::generate_key_url()
                    ]);
                    $insert_multiple = [];
                    foreach ($body["forms"] as $form) {
                        $id_form = $form;
                        $files_selected = $body["files"][$id_form]["selected"];
                        foreach ($files_selected as $file) {

                            $insert_multiple[] = [
                                "id_application" => $id,
                                "id_file" => $file,
                                "id_form" => $id_form,
                            ];
                            $data_applications_files_creation[] = $file;
                        }
                    }
                    DatabaseController::set("application_link_online_files", $insert_multiple, true);
                    return ApplicationsController::createNewFormsApplication($id, $data_applications_files_creation);
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return false;
                }

                return $id;
            }
        ));
        $this->register_api_route("submitApplication/(?P<key>\w+)", array(
            'methods' => 'POST',
            'callback' => function ($request) {
                $key = $request->get_param("key");
                return ApplicationsController::submitApplication($key);
            }
        ));
        $this->register_api_route("deleteClientById/(?P<id>\d+)", array(
            'methods' => 'DELETE',
            'callback' => function ($request) {
                $id = $request->get_param("id");
                $applications = DatabaseController::get("applications", [
                    "where" => "id_client = '$id'"
                ]);
                if ($applications && $applications != "empty") {
                    foreach ($applications as $application) {
                        $id_application = $application["id"];
                        $links = DatabaseController::delete("application_link_online_files", "id_application = '$id_application'");
                        $res = DatabaseController::delete("applications", "id = '$id_application'");
                    }
                }
                $res = DatabaseController::delete("clients", "id = '$id'");
                return $res;
            }
        ));
    }
}
<?php


class ApplicationsController
{

    public static function getTemplatePDF($slug)
    {
        //buscar en la carptea uploads/templates el slug

        $templates_folder = RoutesService::getroot() . "templates_files/$slug";
        error_log("ruta del archivo $templates_folder");


        //si existe el archivo retornar la ruta
        if (file_exists($templates_folder)) {

            return $templates_folder;
        } else {
            error_log("no existe el archivo $slug");
            return false;
        }
    }

    public static function createNewFormsApplication($id_application, $id_files)
    {
        $applications_folder = RoutesService::getupload("applications/application_$id_application", "absolute");

        $files = DatabaseController::get("online_files", [
            "where" => "id IN (" . implode(",", $id_files) . ")"
        ]);

        if (!$files || $files == "empty") {
            return "error";
        }


        if (!file_exists($applications_folder)) {
            mkdir($applications_folder, 0777, true);
        }
        $status_processs = [];
        for ($i = 0; $i < count($files); $i++) {
            $file = $files[$i];
            $url = $file["url"];
            //obtener slug de la url
            $slug = explode("/", $url);

            $name_file = $slug[count($slug) - 1];

            $file_path = $applications_folder . "/" . $name_file;

            if (!file_exists($file_path)) {
                $url = self::getTemplatePDF($name_file);
                if ($url) {
                    $data = file_get_contents($url);
                    file_put_contents($file_path, $data);
                    $status_processs[] = [
                        "id_file" => $file["id"],
                        "id_application" => $id_application,
                        "slug" => $name_file,
                        "status" => "success",
                        "message" => "File created"
                    ];
                } else {
                    $status_processs[] = [
                        "id_file" => $file["id"],
                        "id_application" => $id_application,
                        "status" => "error",
                        "slug" => $name_file,
                        "message" => "File not found"
                    ];
                }
            } else {
                $status_processs[] = [
                    "id_file" => $file["id"],
                    "id_application" => $id_application,
                    "status" => "success",
                    "slug" => $name_file,
                    "message" => "File already exists"
                ];
            }
        }

        return $status_processs;
    }
    public static function submitApplication($key)
    {
        $id_application = DatabaseController::get("applications", [
            "where" => "keyform = '$key'"
        ]);

        if (!$id_application || $id_application == "empty" || $id_application == []) {
            error_log("Fatal Failure: No se encontró la aplicación");
            return false;
        } else {
            $postData = json_decode($id_application[0]["postData"], true);
            $id = $id_application[0]["id"];
        }

        /* GET FILES BY ID APPLICATION */
        $files = DatabaseController::get("application_link_online_files", [
            "where" => "id_application = " . $id
        ]);

        if (!$files || $files == "empty" || $files == []) {
            error_log("Fatal Failure: No se encontraron archivos");
            return false;
        }
        /* GET  FILES */
        $status_processs = self::createNewFormsApplication($id, array_column($files, "id_file"));
        if (!$status_processs || $status_processs == []) {
            error_log("Fatal Failure: No se pudo crear los archivos");
            return false;
        }
        $res = [];
        /* Fill PDFs */
        for ($i = 0; $i < count($status_processs); $i++) {
            $slug = explode(".", $status_processs[$i]["slug"]);
            $id_application = $status_processs[$i]["id_application"];
            $res[] = PdfService::fillForm($slug[0], $postData, $id_application);
        }
        /* cambiar status de aplicaion por to Review */
        DatabaseController::update("applications", [
            "status" => "To Review"
        ], $id_application);


        return $res;
    }
}
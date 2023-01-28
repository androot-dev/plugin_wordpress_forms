<?php

class InmigrationService
{
    public static function provicionalFilesData()
    {
        return [
            [
                "name" => "G-639 | Freedom of Information/Privacy Act and Online FOIA Request",
                "url" => "https://www.uscis.gov/sites/default/files/document/forms/g-639.pdf"
            ]
        ];
    }

    public static function  getForms($category = "all")
    {
        $res = self::getFormsByCategory($category);
        return $res;
    }
    public static function getAllFiles($slug)
    {
        $res = [];
        $res["url"] =  "https://www.uscis.gov" . $slug;
        $scrap = new ScrappingService($res["url"]);


        $res["files"] = $scrap->find([
            "url" => [
                "xpath" => "//*[contains(@class, 'files-listing')]//a",
                "search" => [
                    "attr" => "href"
                ]
            ],
            "name" => [
                "xpath" => "//*[contains(@class, 'files-listing')]//a",
                "search" => [
                    "node" => "nodeValue"
                ]
            ]
        ])->quickClean(["url", "name"])->clean(function (&$res, $key) {

            if (!$res["url"]) {

                //buscar en provicional
                $prov = self::provicionalFilesData();
                foreach ($prov as $p) {
                    if ($p["name"] == $res["name"]) {
                        $res["url"] = $p["url"];
                        break;
                    }
                }
            } else {
                $res["url"] = "https://www.uscis.gov" . $res["url"];
            }
        })->get();

        $res["description"] = $scrap->clean()->find([
            "description" => [
                "xpath" => "//div[@class='content-section']//div[contains(concat(' ', normalize-space(@class), ' '), ' field--name-body ') and contains(concat(' ', normalize-space(@class), ' '), ' field__item ')]//p[position() < 3]",
                "search" => [
                    "node" => "nodeValue"
                ]
            ]
        ])->clean(function (&$res, $key) {
            $res = implode("", $res);
        })->get()[0];



        return $res;
    }
    public static function getFormBySlug($id)
    {
        $scrapping = new ScrappingService("https://www.uscis.gov/forms/all-forms");
        $response = $scrapping->search([
            "attr:href" => "//div[contains(@class, 'views-field')]//a[@href='/$id']",
            "node:nodeValue" => "//div[contains(@class, 'views-field')]//a[@href='/$id']",
        ])->clean(function (&$res, $key) {
            $length = count($res);
            for ($i = 0; $i < $length; $i++) {
                $new_val = [];
                if ($key == 0) {
                    $new_val = self::getAllFiles($res[$i]);
                }
                if ($key == 1) {
                    $new_val["name"] = $res[$i];
                }
                $res[$i] = $new_val;
            }
        })->link()->get();
        return $response;
    }
    public static function getCategories()
    {
        $categories = [
            "Forms" => [
                "id" => 2671,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=2671&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Adoptions-Based Forms" => [
                "id" => 1127,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1127&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Citizenship and Naturalization-Based  Forms" => [
                "id" => 1128,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1128&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Employment-Based Forms" => [
                "id" => 1129,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1129&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Family-Based Forms" => [
                "id" => 1130,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1130&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Green Card-Based Forms" => [
                "id" => 1131,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1131&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "Humanitarian Benefits-Based Forms" => [
                "id" => 1132,
                "url" => "https://www.uscis.gov/forms/all-forms?topic_id[]=1132&sort_bef_combine=sticky_ASC&ftopics_tid=0"
            ],
            "all" => [
                "id" => "all",
                "url" => "https://www.uscis.gov/forms/all-forms"
            ]
        ];
        return $categories;
    }
    public static function getFormsByCategory($id)
    {
        $categories = self::getCategories();
        //buscar donde esta el id
        $select = array_filter($categories, function ($item) use ($id) {
            return $item["id"] == $id;
        });

        if (count($select) > 0) {
            $key = array_keys($select)[0];
            $id = $select[$key]["url"];
        } else {
            return [];
        }

        $scrapping = new ScrappingService($id);
        $response = $scrapping->search([
            "attr:href" => "//div[contains(@class, 'views-row')]/div[contains(@class, 'views-field')]/span[contains(@class, 'field-content')]/a",
            "node:nodeValue" => "//div[contains(@class, 'views-row')]/div[contains(@class, 'views-field')]/span[contains(@class, 'field-content')]/a",
        ])->clean(function (&$res, $key) {
            $length = count($res);
            for ($i = 0; $i < $length; $i++) {
                $new_val = [];
                if ($key == 0) {
                    $new_val = self::getAllFiles($res[$i]);
                }
                if ($key == 1) {
                    $new_val["name"] = $res[$i];
                }
                $res[$i] = $new_val;
            }
        })->link()->get();

        return $response;
    }
    public static function syncForms($slug = "all")
    {
        global $permited;

        $res = [
            "status" => "404",
            "data" => []
        ];

        if ($slug == "all" || !$slug) {
            $categories = self::getForms("all");
            $database_forms = DatabaseController::get("forms");
            $res["data"] = $categories;
            $res["status"] = "200";

            if ($database_forms && $database_forms != "empty") {
                foreach ($database_forms as $database_form) {
                    foreach ($res["data"] as $key => $response_form) {
                        if ($database_form["name"] == $response_form["name"]) {
                            unset($res["data"][$key]);
                        }
                    }
                }
            }
            if ($permited) {
                foreach ($res["data"] as $key => $response_form) {
                    $shortname = trim(strtoupper(explode("|", $response_form["name"])[0]));
                    $res["data"][$key]["shortname"] = $shortname;
                    if (!in_array($shortname, $permited)) {
                        unset($res["data"][$key]);
                    }
                }
            }
            //registrar en la base de datos
            //error_log("syncForms: " . json_encode($response["data"]));
            global $wpdb;
            $wpdb->query('START TRANSACTION');
            foreach ($res["data"] as $key => $response_form) {
                $data = [
                    "name" => $response_form["name"],
                    "description" => $response_form["description"],
                    "categories" => "[2070]",
                    "url" => $response_form["url"]
                ];
                try {
                    $response = DatabaseController::set("forms", $data);

                    if ($response) {
                        //registrar files
                        foreach ($response_form["files"] as $file) {
                            $data = [
                                "name" => $file["name"],
                                "url" => $file["url"]
                            ];
                            $response2 = DatabaseController::set("online_files", $data);

                            if ($res) {
                                $data = [
                                    "id_form" => $response,
                                    "id_file" => $response2
                                ];
                                DatabaseController::set("online_files_link_forms", $data);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log(print_r($e, true));
                    $wpdb->query('ROLLBACK');
                    $res["status"] = "404";
                    $res["data"] = [];
                    $res["message"] = "Ops! Something went wrong";
                }
            }
            $wpdb->query('COMMIT');
            $res["status"] = "200";
            $res["message"] = "Forms syncronized";
            return $res;
        } else {
            $getFormBySlug = self::getFormBySlug($slug);
            $database_forms = DatabaseController::get(
                "forms",
                ["where" => "url = https://www.uscis.gov/forms/$slug"]
            );
            $response["data"] = $getFormBySlug;
            $response["status"] = "success";

            return $response;
        }
    }
}
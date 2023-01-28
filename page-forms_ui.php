<?php

/**
 * Template Name: myadmin
 */

$list_questions = [];
//obtener el ultimo registro

$key = $_GET["key"];
if (!$key) {
    exit;
}

class ApplicationForm
{
    public function __construct($key)
    {
        $this->key = $key;
        $this->groups = [];
        $this->files_applicattion = [];
        $this->questions_groups = [];
        $this->application_post_data = [];
        $this->id_application = null;
        $this->id_client = null;
        $this->name_client = null;
        $this->status = null;
    }
    public  function getApplicationByKey()
    {
        $key = $this->key;

        $id_application = DatabaseController::get("applications", [
            "where" => "keyform = '" . $key . "'"
        ]);

        $this->status = $id_application[0]["status"];
        $this->id_client = $id_application[0]["id_client"];
        $this->name_client = DatabaseController::get("clients", [
            "where" => "id = " . $this->id_client,
            "select" => "name"
        ])[0]["name"];

        if (!$id_application || $id_application == "empty" || $id_application == []) {
            return false;
        } else {
            $this->application_post_data = json_decode($id_application[0]["postData"], true);
            $this->id_application = $id_application[0]["id"];
            return $id_application[0]["id"];
        }
    }
    public function getFilesByApplicationId($id_application)
    {
        $id_files = DatabaseController::get("application_link_online_files", [
            "where" => "id_application = " . $id_application,
            "order" => "id ASC"
        ]);

        $files = [];
        foreach ($id_files as $key => $value) {
            $data =  DatabaseController::get("online_files", [
                "where" => "id = " . $value["id_file"]
            ]);
            foreach ($data as $key => $value2) {

                $data[$key]["form"] = $value["id_form"];
                $data[$key]["form"] = DatabaseController::get("forms", [
                    "where" => "id = " . $value["id_form"],
                    "select" => "name, id"
                ])[0];
            }
            $files[] = $data[0];


            $this->files_applicattion[] = $data[0];
        }
        if (!$files || $files == "empty" || $files == []) {
            return false;
        }
        return $files;
    }
    private function getQuestionsByIdFiles($files)
    {
        $questions = [];
        foreach ($files as $key => $value) {
            $form = $value["form"];
            $id_questions = DatabaseController::get("questions_link_online_files", [
                "where" => "id_file = " . $files[$key]["id"],
                "order" => "order_question ASC"
            ]);
            if (!$id_questions || $id_questions == "empty" || $id_questions == []) {
                continue;
            }

            foreach ($id_questions as $key2 => $value2) {
                $data =  DatabaseController::get("questions", [
                    "where" => "id = " . $value2["id_question"],
                ]);
                $data[0]["form"] = $form;
                $data[0]["required"] = $value2["required"];
                array_push($questions, $data[0]);
            }
        }
        if (!$questions || $questions == "empty" || $questions == []) {
            return false;
        }


        return $questions;
    }
    public function groupByForm($questions)
    {
        $formularios = [];
        foreach ($questions as $key => $value) {
            $formularios[$value["form"]["name"]][] = $value;
        }

        return $formularios;
    }
    public function getGroupsByFiles()
    {
        $ids = $this->files_applicattion;

        $groups = [];

        foreach ($ids as $key => $value) {

            $id = $value["id"];

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
                $groups[$id] = $group;
            }
        }

        $this->groups = $groups;

        return $this->groups;
    }
    public function getQuestionsInGroup()
    {
        $groups = $this->groups;
        $questions_group = [];
        foreach ($groups as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $data = DatabaseController::get("questions_groups", [
                    "select " => "questions_ids",
                    "where" => " name = '" . $value2["name"] . "'"
                ]);

                if ($data && $data != "empty") {
                    $questions_ids = $data[0]["questions_ids"];
                    $questions_ids = explode(",", $questions_ids);
                    $questions_ids = implode("','", $questions_ids);
                    $questions = DatabaseController::get("questions", [
                        "where" => " id IN ('$questions_ids')",
                        "order" => "id ASC"
                    ]);
                    $questions_group[$value2["name"]] = $questions;
                }
            }
        }
        $this->questions_groups = $questions_group;
        return $this->questions_groups;
    }
    public function get()
    {
        $id_application =  $this->getApplicationByKey();
        if (!$id_application) {
            return false;
        }
        $files = $this->getFilesByApplicationId($id_application);
        if (!$files) {
            return false;
        }
        $questions = $this->getQuestionsByIdFiles($files);
        if (!$questions) {
            //return false;
        }

        $questions = $this->groupByForm($questions);
        $this->getGroupsByFiles();
        $this->getQuestionsInGroup();

        return $questions;
    }
    public function fillHtmlFormWithPostData($question_id)
    {
        $fill = $this->application_post_data;
        if ($fill) {
            foreach ($fill as $key => $value) {
                if ($value["id_question"] == $question_id) {
                    return $value["answer"];
                }
            }
        }
        return false;
    }
}
new ViewController("applications", ["update"]);

$application = new ApplicationForm($key);
$questions = $application->get();
$list_questions = [];
$list_grouping_fieldset = [];
foreach ($questions as $key => $value) {
    $list_questions[$key] = [];
    foreach ($value as $key2 => $question) {
        $list_questions[$key][] = [
            "id" => $question["id"],
            "type" => $question["type"],
            "required" => 0,
            "options" => json_decode($question["options"], true)["options"],
            "methods" => json_decode($question["options"], true)["methods"],
            "hooks" => json_decode($question["options"], true)["hooks"] ?? [],
            //remplazar si existe {{name}} por el nombre del cliente
            "question" => str_replace("{{name}}", $application->name_client, $question["question"]),
            "name_pdf_field" => $question["name_pdf_field"],
            "placeholder" => $question["placeholder"],
            "form" => $key,
            "answer" => $application->fillHtmlFormWithPostData($question["id"]),
            "class" => $question["class"]
        ];
    }
}

$quest_groups = $application->questions_groups;
$list_quest_groups = [];


foreach ($quest_groups as $key => $value) {
    $list_quest_groups[$key] = [];

    foreach ($value as $key2 => $question) {
        $list_quest_groups[$key][] = [
            "id" => $question["id"],
            "type" => $question["type"],
            "required" => 0,
            "options" => json_decode($question["options"], true)["options"],
            "methods" => json_decode($question["options"], true)["methods"],
            "hooks" => json_decode($question["options"], true)["hooks"] ?? [],
            //remplazar si existe {{name}} por el nombre del cliente
            "question" => str_replace("{{name}}", $application->name_client, $question["question"]),
            "name_pdf_field" => $question["name_pdf_field"],
            "placeholder" => $question["placeholder"],
            "form" => $key,
            "answer" => $application->fillHtmlFormWithPostData($question["id"]),
            "class" => $question["class"]
        ];
    }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= RoutesService::getresource("css/theme.css") ?>">
    <link rel="stylesheet" href="<?= RoutesService::getresource("css/bootstrap.css") ?>">
    <link rel="stylesheet" href="<?= RoutesService::getresource("css/primeicons.css") ?>">
    <link rel="stylesheet" href="<?= RoutesService::getresource("css/primevue.css") ?>">
    <link rel="stylesheet" href="<?= RoutesService::getresource("css/theme.prime-light.css") ?>">
    <title>Application - <?= $application->name_client ?></title>
</head>

<body class="d-flex justify-content-center ">
    <div class="flex-column justify-content-center align-items-center container-loader bg-light top-0 position-fixed loader-before-vue w-100 h-100"
        style="z-index:1; display:flex;">
        <div class="lds-ripple ">
            <div></div>
            <div></div>
        </div>
        <?php if ($application->status == "success"  && !current_user_can('administrator')) : ?>
        Sorry, this application is already completed
        <?php endif; ?>
    </div>
    <div class="d-flex flex-column contianer-global w-100" style="max-width: 800px;">

        <?php if ($application->status != "success" || current_user_can('administrator')) : ?>
        <?php
            RoutesService::get_template_part("header.php", array(
                "title" => "Please answer the following questions"
            ));
            ?>
        <div class=" w-100 container">

            <div class="container-form" id="app">
                <!--PESTAÑA PRINCIPAL - PREGUNTAS DEL FORMULARIO -->
                <p-tabview scrollable @tab-change="changeIndexTab" v-model:activeIndex="activeIndexTabView">
                    <p-tabpanel header="Application" v-if="proxyIs(list_questions)">
                        <div class="row p-4 ">
                            <div v-for="(form, key) in list_questions" class="container-form-questions">
                                <h4 class="w-100 text-center">{{key}}</h4>
                                <div :class="`container-input ${question.class ? question.class : `` }`"
                                    v-for="(question, index) in form" :key="index">

                                    <div v-if="question.type == 'text' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : ``}`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-inputtext v-model="question.answer" :required="question.required"
                                            :placeholder="question.placeholder">
                                        </p-inputtext>

                                    </div>

                                    <div v-if="question.type == 'number' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-inputnumber v-model="question.answer" :required="question.required"
                                            :placeholder="question.placeholder">
                                        </p-inputnumber>

                                    </div>

                                    <div v-if="question.type == 'textarea' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-textarea v-model="question.answer" :required="question.required"
                                            :placeholder="question.placeholder">
                                        </p-textarea>


                                    </div>

                                    <div v-if="question.type == 'select' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-dropdown v-model="question.answer" :options="question.options"
                                            :required="question.required">
                                        </p-dropdown>
                                    </div>

                                    <div v-if="question.type == 'radio' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <div class="field-checkbox radio" v-for="(option, index) in question.options">
                                            <p-radiobutton :key="index" :value="option" v-model="question.answer"
                                                :label="option" :required="question.required" name="question.answer">
                                            </p-radiobutton>
                                            <label class=" ms-2 p-checkbox-label">{{option}}</label>
                                        </div>
                                    </div>

                                    <div v-if="question.type == 'checkbox' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <div class="field-checkbox " v-for="(option, index) in question.options"
                                            :key="index">
                                            <p-checkbox v-model="question.answer" :value="option" :label="option"
                                                :required="question.required">
                                            </p-checkbox>
                                            <label class=" ms-2 p-checkbox-label">{{option}}</label>


                                        </div>
                                    </div>

                                    <div v-if="question.type == 'date' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-calendar v-model="question.answer" :required="question.required"
                                            dateFormat="dd.mm.yy" :placeholder="question.placeholder">
                                        </p-calendar>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </p-tabpanel>
                    <p-tabpanel v-for="(list, index) in list_quest_groups" :key="index" :header="index">



                        <div class="row p-4 ">
                            <h4 class="w-100 text-center">{{index}}</h4>
                            <div class="container-form-questions mt-0 form-grid-2">
                                <div :class="`container-input ${question.class ? question.class : ``}`"
                                    v-for="(question, key) in list" :key="key">

                                    <div v-if="question.type == 'text' && question.show" :class="`d-flex flex-column
                                        p-3 ${question.hidden ? `hidden_force`  : `` }`">
                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-inputtext v-model="question.answer" :placeholder="question.placeholder"
                                            :required="question.required">
                                        </p-inputtext>

                                    </div>

                                    <div v-if="question.type == 'number' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-inputnumber v-model="question.answer" :placeholder="question.placeholder"
                                            :required="question.required">
                                        </p-inputnumber>

                                    </div>

                                    <div v-if="question.type == 'textarea' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-textarea v-model="question.answer" :placeholder="question.placeholder"
                                            :required="question.required">
                                        </p-textarea>


                                    </div>

                                    <div v-if="question.type == 'select' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-dropdown v-model="question.answer" :options="question.options"
                                            :required="question.required">
                                        </p-dropdown>
                                    </div>

                                    <div v-if="question.type == 'radio' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <div class="field-checkbox radio" v-for="(option, index) in question.options">
                                            <p-radiobutton :key="index" :value="option" v-model="question.answer"
                                                :label="option" :required="question.required" name="question.answer">
                                            </p-radiobutton>
                                            <label class=" ms-2 p-checkbox-label">{{option}}</label>
                                        </div>
                                    </div>

                                    <div v-if="question.type == 'checkbox' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <div class="field-checkbox " v-for="(option, index) in question.options"
                                            :key="index">
                                            <p-checkbox v-model="question.answer" :value="option" :label="option"
                                                :required="question.required">
                                            </p-checkbox>
                                            <label class=" ms-2 p-checkbox-label">{{option}}</label>


                                        </div>
                                    </div>

                                    <div v-if="question.type == 'date' && question.show"
                                        :class="`d-flex flex-column p-3 ${question.hidden ? `hidden_force`  : `` }`">

                                        <label class="p-inputtext-label">{{question.question}}</label>
                                        <p-calendar v-model="question.answer" :required="question.required"
                                            dateFormat="dd.mm.yy">
                                        </p-calendar>

                                    </div>

                                </div>

                            </div>



                        </div>
                    </p-tabpanel>
                </p-tabview>
                <div class="d-flex justify-content-center">
                    <p-button label="Submit" class="my-5 p-button-raised p-button-rounded " @click="submit"
                        style="width:80%;">
                    </p-button>
                </div>
            </div>
        </div>
        <?php else : ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="text-center">This form is not available</h1>
                </div>
            </div>
        </div>
        <?php endif; ?>


</body>

</html>
<script src="<?= RoutesService::getresource("js/vue.global.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/primevue.core.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/sweetalert.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/bootstrap.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/checkbox.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/textarea.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/calendar.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/radiobutton.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/tabview.js") ?>"></script>
<script src="<?= RoutesService::getresource("js/components/tabpanel.js") ?>"></script>
<?php if ($application->status != "success" || current_user_can('administrator')) : ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const {
        createApp,
        ref,
        computed,
        onMounted,
        reactive,
        watchEffect,

    } = Vue

    const app = {
        components: {
            "p-inputtext": primevue.inputtext,
            "p-button": primevue.button,
            "p-textarea": primevue.textarea,
            "p-dropdown": primevue.dropdown,
            "p-checkbox": primevue.checkbox,
            "p-inputnumber": primevue.inputnumber,
            "p-calendar": primevue.calendar,
            "p-radiobutton": primevue.radiobutton,
            "p-tabview": primevue.tabview,
            "p-tabpanel": primevue.tabpanel
        },
        methods: {
            changeIndexTab(event) {
                this.activeIndexTabView = event.index;
            },
            findShortcuts(field) {
                let regex = /#\{(.+?)\}/g;
                let match = regex.exec(field);
                if (match) {
                    let field = match[1];
                    let header = `<h4 class="heading-field">${field}</h4>`;
                    field = field.replace(field, "");
                    return header;
                }
                return "";
            },
            nameshort(name) {
                let name_short = name.substring(0, name.indexOf("|"));
                return name_short;
            },
            async submit() {
                let post = [];
                //si estan vacias todas no enviar 
                let empty = false;
                for (let i in this.list_questions) {
                    for (let question in this.list_questions[i]) {

                        if (this.list_questions[i][question].show == false ||
                            !this.list_questions[i][question].answer ||
                            this.list_questions[i][question].answer == [] ||
                            this.list_questions[i][question].answer == {}
                        ) {
                            continue;
                        }
                        post.push({
                            id_question: this.list_questions[i][question].id,
                            form: this.list_questions[i][question].form,
                            answer: this.list_questions[i][question].answer,
                            name_pdf_field: this.list_questions[i][question]
                                .name_pdf_field,
                        })
                        empty = true;
                    }
                }

                for (let i in this.list_quest_groups) {
                    for (let question in this.list_quest_groups[i]) {

                        if (this.list_quest_groups[i][question].show == false ||
                            !this.list_quest_groups[i][question].answer ||
                            this.list_quest_groups[i][question].answer == [] ||
                            this.list_quest_groups[i][question].answer == {}
                        ) {
                            continue;
                        }
                        post.push({
                            id_question: this.list_quest_groups[i][question].id,
                            form: this.list_quest_groups[i][question].form,
                            answer: this.list_quest_groups[i][question].answer,
                            name_pdf_field: this.list_quest_groups[i][question]
                                .name_pdf_field,
                        })
                        empty = true;
                    }
                }

                if (!empty) {
                    Swal.fire({
                        title: "Error!",
                        text: "You must answer at least one question!",
                        icon: "error",
                        button: "Ok",
                    });
                    return;
                }
                let res = await Applications.update("<?= $application->getApplicationByKey() ?>", {
                    "postData": JSON.stringify(post)
                });

                if (res) {
                    Swal.fire({
                        title: "Success!",
                        text: "Your form has been submitted successfully!",
                        icon: "success",
                        button: "Ok",
                    }).then((value) => {
                        fetch("<?= RoutesService::get_api_base() ?>submitApplication/<?= $_GET["key"]; ?>", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            }
                        }).finally(() => {
                            // window.location.href = "<?= site_url() ?>";
                        });
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: "An error has occurred!",
                        icon: "error",
                        button: "Ok",
                    });
                }

            }
        },
        setup() {
            const list_questions = reactive(<?php echo json_encode($list_questions); ?>);
            const list_quest_groups = reactive(<?php echo json_encode($list_quest_groups); ?>);

            class Hooks {
                constructor() {
                    this.hooks = {};
                }

                static localaddress_to_mailingaddress() {
                    let local_ids_address = [15, 21, 22, 23, 24, 25, 26, 27, 28, 29];
                    let mailing_ids_address = [46, 47, 48, 49, 50, 51, 52, 53, 54, 55];
                    for (let i in local_ids_address) {
                        let list = list_quest_groups["General"];
                        let local = list.find((item) => item.id == local_ids_address[i]);
                        let mailing = list.find((item) => item.id == mailing_ids_address[
                            i]);

                        if (mailing) {
                            mailing.answer = local.answer;
                            mailing.hidden = true;
                            setTimeout(() => {
                                mailing.show = true;
                            }, 100);
                        }

                    }
                }
                static off_localaddress_to_mailingaddress() {
                    let local_ids_address = [15, 21, 22, 23, 24, 25, 26, 27, 28, 29];
                    let mailing_ids_address = [46, 47, 48, 49, 50, 51, 52, 53, 54, 55];
                    for (let i in local_ids_address) {
                        let list = list_quest_groups["General"];
                        let local = list.find((item) => item.id == local_ids_address[i]);
                        let mailing = list.find((item) => item.id == mailing_ids_address[
                            i]);
                        if (mailing) {
                            mailing.answer = mailing.default;
                            mailing.hidden = false;
                        }
                    }
                }
            }


            function proxyIs(proxy) {
                for (let i in proxy) {
                    return true;
                }
                return false;
            }
            const activeIndexTabView = ref(0);
            const index_tab = ref(0);


            for (let list in list_questions) {
                for (let i in list_questions[list]) {
                    if (list_questions[list][i].type == "checkbox") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : [];
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "radio") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : [];
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "select") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : list_questions[list][i].options[0];
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "date") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            new Date(
                                list_questions[list][i].answer) : "";
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "number") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : null;
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "text") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : "";
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "textarea") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : "";
                        list_questions[list][i].answer = list_questions[list][i].default;

                    } else if (list_questions[list][i].type == "file") {
                        list_questions[list][i].default = list_questions[list][i].answer ?
                            list_questions[
                                list][i].answer : "";
                        list_questions[list][i].answer = list_questions[list][i].default;
                    }
                    list_questions[list][i].show = true;



                    /* verificar si debe ocultarse o mostrarse de acuerdo a lo establecido en sus opciones 
                        con only_show y value_is

                    only_show: id de la pregunta que debe estar establecida para que se muestre
                    value_is: valor que debe tener la pregunta(only_show) para que se muestre si esta en blanco
                    solo de verifica si esta establecido algun valor

                    example: 

                      {"methods":{"only_show":"31","value_is":["Saturno"]},"options":[]}

                    */

                    watchEffect(() => {
                        if (list_questions[list][i].methods?.only_show) {
                            let only_show = list_questions[list][i].methods.only_show;
                            let value_is = list_questions[list][i].methods.value_is;
                            let show = false;
                            for (let j in list_questions[list]) {

                                if (list_questions[list][j].id == only_show) {

                                    if (value_is) {
                                        if (list_questions[list][j].answer ==
                                            value_is) {
                                            show = true;
                                        }
                                    } else {
                                        if (list_questions[list][j].answer) {
                                            show = true;
                                        }
                                    }
                                }
                            }
                            list_questions[list][i].show = show;
                        }
                    })

                    if (list_questions[list][i].hooks) {
                        watchEffect(() => {
                            if (list_questions[list][i].hooks.is_equals) {
                                //verificar el aswer de este campo y si es igual a is_queals ejecutar el hook
                                if (list_questions[list][i].answer == list_questions[
                                        list][i].hooks
                                    .is_equals) {

                                    if (list_questions[list][i].hooks.on) {
                                        let fn = list_questions[list][i].hooks["on"];
                                        Hooks[fn]();
                                    }

                                } else {
                                    if (list_questions[list][i].hooks.off) {
                                        let fn = list_questions[list][i].hooks["off"];
                                        Hooks[fn]();
                                    }
                                }
                            }
                        })
                    }

                }

            }

            for (let list in list_quest_groups) {
                for (let i in list_quest_groups[list]) {
                    if (list_quest_groups[list][i].type == "checkbox") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : [];
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "radio") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : [];
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "select") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : list_quest_groups[list][i].options[0];
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "date") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            new Date(list_quest_groups[list][i].answer) : "";
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "number") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : null;
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "text") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : "";
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "textarea") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : "";
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;

                    } else if (list_quest_groups[list][i].type == "file") {
                        list_quest_groups[list][i].default = list_quest_groups[list][i].answer ?
                            list_quest_groups[
                                list][i].answer : "";
                        list_quest_groups[list][i].answer = list_quest_groups[list][i].default;
                    }
                    list_quest_groups[list][i].show = true;

                    /* verificar si debe ocultarse o mostrarse de acuerdo a lo establecido en sus opciones 
                        con only_show y value_is

                    only_show: id de la pregunta que debe estar establecida para que se muestre
                    value_is: valor que debe tener la pregunta(only_show) para que se muestre si esta en blanco
                    solo de verifica si esta establecido algun valor

                    example: 

                      {"methods":{"only_show":"31","value_is":["Saturno"]},"options":[]}

                    */

                    watchEffect(() => {
                        if (list_quest_groups[list][i].methods?.only_show) {
                            let only_show = list_quest_groups[list][i].methods
                                .only_show;
                            let value_is = list_quest_groups[list][i].methods.value_is;
                            let show = false;
                            for (let j in list_quest_groups[list]) {

                                if (list_quest_groups[list][j].id == only_show) {

                                    if (value_is) {
                                        if (list_quest_groups[list][j].answer ==
                                            value_is) {
                                            show = true;
                                        }
                                    } else {
                                        if (list_quest_groups[list][j].answer) {
                                            show = true;
                                        }
                                    }
                                }
                            }
                            list_quest_groups[list][i].show = show;
                        }
                    })
                    if (list_quest_groups[list][i].hooks) {

                        watchEffect(() => {

                            if (list_quest_groups[list][i].hooks.is_equals) {
                                //verificar el aswer de este campo y si es igual a is_queals ejecutar el hook
                                if (list_quest_groups[list][i].answer ==
                                    list_quest_groups[list][i]
                                    .hooks.is_equals) {

                                    if (list_quest_groups[list][i].hooks.on) {
                                        let fn = list_quest_groups[list][i].hooks["on"];
                                        Hooks[fn]();
                                    }

                                } else {
                                    if (list_quest_groups[list][i].hooks.off) {
                                        let fn = list_quest_groups[list][i].hooks[
                                            "off"];
                                        Hooks[fn]();
                                    }
                                }
                            }
                        })
                    }
                }
            }
            onMounted(() => {
                let loader = document.querySelector(".loader-before-vue");
                loader.style.transition = "all 0.5s";
                loader.style.opacity = "0";
                setTimeout(() => {
                    loader.style.display = "none";
                }, 500);
                let separator_before = document.querySelectorAll(".separator-before");
                let separator_after = document.querySelectorAll(".separator-after");
                if (separator_before) {
                    let sep = document.createElement("div");
                    sep.classList.add("separator-vue");
                    sep.classList.add("field-expanded");

                    separator_before.forEach((item) => {

                        //insertar como hermano antes

                        item.parentNode.insertBefore(sep.cloneNode(true), item);

                    });

                }
                if (separator_after) {
                    let sep = document.createElement("div");
                    sep.classList.add("separator-vue");
                    sep.classList.add("field-expanded");

                    separator_after.forEach((item) => {

                        //insertar como hermano despues

                        item.parentNode.insertBefore(sep.cloneNode(true), item
                            .nextSibling);

                    });

                }
            });

            return {
                list_questions,
                index_tab,
                list_quest_groups,
                activeIndexTabView,
                proxyIs
            }
        }
    }
    createApp(app).use(primevue.config.default).directive('tooltip', primevue.tooltip).mount(
        "#app");
});
</script>
<?php endif; ?>
<style>
body {
    background-color: #f5f5f5;
}

.separator-vue {
    margin: 25px auto 20px auto;
    height: 3px;
    background: #bdbdbd;
    opacity: 0.3;
    border-radius: 15px;
    width: 80%;
}

.container-form {

    padding: 20 px;
    border-radius: 10 px;
    box-shadow: 0 0 10 px 0 rgba(0, 0, 0, 0.1);
}

.p-inputtext-label {
    margin-bottom: 15px;
}

.container-form-questions {
    padding-top: 50px;
    padding-bottom: 50px;
    margin-top: 30px;
    background-color: #fff;
}

.hidden_force {
    display: none !important;
}
</style>
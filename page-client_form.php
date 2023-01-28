<?php

/**
 * Template Name: myadmin
 */

$list_questions = [];
//obtener el ultimo registro


$id_form = $_GET["id"];
if (!$id_form) {
    exit;
}
$questions_link = DatabaseController::get("questions_link_online_files", [
    "where" => "id_file = $id_form",
    "order" => "order_question ASC",
]);

if (!$questions_link || $questions_link == "empty") {
    exit;
}
foreach ($questions_link as $key => $value) {
    $where_or .= "id = " . $value["id_question"] . " OR ";
}
$questions = DatabaseController::get("questions", [
    "where" => substr($where_or, 0, -3),
    "order" => "id ASC"
]);
foreach ($questions as $key => $value) {
    $list_questions[$questions[$key]["question"]] = [
        "type" => $value["type"],
        "required" => 0,
        "options" => json_decode($value["options"], true)["options"],
        "methods" => json_decode($value["options"], true)["methods"]
    ];
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
    <title>Form</title>
</head>

<body class="d-flex justify-content-center">
    <div class="justify-content-center align-items-center container-loader bg-light top-0 position-fixed loader-before-vue w-100 h-100"
        style="z-index:1; display:flex;">
        <div class="lds-ripple ">
            <div></div>
            <div></div>
        </div>
    </div>
    <div class="d-flex flex-column contianer-global" style="max-width: 800px;">
        <?php
        RoutesService::get_template_part("header.php", array(
            "title" => "Please answer the following questions"
        ));
        ?>
        <div class=" w-100 container mt-5">
            <div class="container-form" id="app">
                <div class="row p-4">
                    <div class="container-input " v-for="(question, index) in list_questions" :key="index">


                        <div v-if="question.type == 'text' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <p-inputtext v-model="question.answer" :label="question.label"
                                :required="question.required">
                            </p-inputtext>

                        </div>

                        <div v-if="question.type == 'number' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <p-inputnumber v-model="question.answer" :label="question.label"
                                :required="question.required">
                            </p-inputnumber>

                        </div>

                        <div v-if="question.type == 'textarea' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <p-textarea v-model="question.answer" :label="question.label" :required="question.required">
                            </p-textarea>


                        </div>

                        <div v-if="question.type == 'select' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <p-dropdown v-model="question.answer" :options="question.options"
                                :required="question.required">
                            </p-dropdown>
                        </div>

                        <div v-if="question.type == 'radio' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <div class="field-checkbox radio" v-for="(option, index) in question.options">
                                <p-radiobutton :key="index" :value="option" v-model="question.answer" :label="option"
                                    :required="question.required" name="question.answer">
                                </p-radiobutton>
                                <label class=" ms-2 p-checkbox-label">{{option}}</label>
                            </div>
                        </div>

                        <div v-if="question.type == 'checkbox' && question.show" class="d-flex flex-column p-3">

                            <label class="p-inputtext-label">{{index}}</label>
                            <div class="field-checkbox " v-for="(option, index) in question.options" :key="index">
                                <p-checkbox v-model="question.answer" :value="option" :label="option"
                                    :required="question.required">
                                </p-checkbox>
                                <label class=" ms-2 p-checkbox-label">{{option}}</label>


                            </div>
                        </div>

                        <div v-if="question.type == 'date' && question.show" class="d-flex flex-column p-3 ">

                            <label class="p-inputtext-label">{{index}}</label>
                            <p-calendar v-model="question.answer" :required="question.required" dateFormat="dd.mm.yy">
                            </p-calendar>

                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <p-button label="Submit" class="my-5 p-button-raised p-button-rounded " @click="submit"
                            style="width:80%;">
                        </p-button>
                    </div>
                </div>
            </div>
        </div>

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
        },
        methods: {
            nameshort(name) {
                //capturar desde 0 hasta el primer |
                let name_short = name.substring(0, name.indexOf("|"));
                return name_short;
            },
            submit() {
                let answers = [];
                for (let i in this.list_questions) {
                    answers.push(this.list_questions[i].answer);
                }
                console.log(answers);
            },
            wacthShowIf(list_questions, i) {
                let answer = this.list_questions.answer;
                let question = i;
                for (let j in list_questions) {
                    let onlyShow = list_questions[j].methods.only_show;
                    let valueIs = list_questions[j].methods.value_is;

                    if (onlyShow) {
                        if (onlyShow == question) {
                            if (answer == valueIs) {
                                list_questions[j].show = true;
                            } else {
                                list_questions[j].show = false;
                            }
                        }
                    }
                }
            }
        },

        setup() {
            const list_questions = reactive(<?php echo json_encode($list_questions); ?>);

            for (let i in list_questions) {

                if (list_questions[i].type == "checkbox") {
                    list_questions[i].answer = [];
                } else if (list_questions[i].type == "radio") {
                    list_questions[i].answer = [];
                } else if (list_questions[i].type == "select") {
                    list_questions[i].answer = list_questions[i].options[0];
                } else if (list_questions[i].type == "date") {
                    list_questions[i].answer = "";
                } else if (list_questions[i].type == "number") {
                    list_questions[i].answer = null;
                } else if (list_questions[i].type == "text") {
                    list_questions[i].answer = "";
                }
                list_questions[i].show = true;
                watchEffect(() => {
                    let answer = list_questions[i].answer;
                    let question = i;
                    for (let j in list_questions) {
                        let onlyShow = list_questions[j]?.methods?.only_show ? list_questions[j]
                            .methods.only_show : null;
                        let valueIs = list_questions[j]?.methods?.value_is ? list_questions[j]
                            .methods.value_is : null;

                        if (onlyShow) {
                            if (onlyShow == question) {
                                if (answer == valueIs) {
                                    list_questions[j].show = true;
                                } else {
                                    list_questions[j].show = false;
                                }
                            }
                        }
                    }
                });
                onMounted(() => {
                    let answer = list_questions[i].answer;
                    let question = i;
                    for (let j in list_questions) {

                        let onlyShow = list_questions[j]?.methods?.only_show ? list_questions[j]
                            .methods.only_show : null;
                        let valueIs = list_questions[j]?.methods?.value_is ? list_questions[j]
                            .methods.value_is : null;

                        if (onlyShow) {
                            if (onlyShow == question) {
                                if (answer == valueIs) {
                                    list_questions[j].show = true;
                                } else {
                                    list_questions[j].show = false;
                                }
                            }
                        }
                    }
                });
                onMounted(() => {
                    let loader = document.querySelector(".loader-before-vue");
                    loader.style.transition = "all 0.5s";
                    loader.style.opacity = "0";
                    setTimeout(() => {
                        loader.style.display = "none";
                    }, 500);
                });


            }


            return {
                list_questions



            }
        }
    }
    createApp(app).use(primevue.config.default).directive('tooltip', primevue.tooltip).mount(
        "#app");
});
</script>

<style>
body {
    background-color: #f5f5f5;
}

.container-form {
    background-color: #fff;
    padding: 20 px;
    border-radius: 10 px;
    box-shadow: 0 0 10 px 0 rgba(0, 0, 0, 0.1);
}

.p-inputtext-label {
    margin-bottom: 15px;
}
</style>
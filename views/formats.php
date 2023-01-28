<?php
//obtener instancia de wpdb
$db = new DatabaseController();


$param_page = isset($_GET["nr_page"]) ? $_GET["nr_page"] : 1;
$per_page = 110;

$data = $db->get("forms", [
    "order" => "id ASC",
    "limit" => $per_page,
    "offset" => ($param_page - 1) * $per_page
]);

$data = $data == "empty" ? [] : $data;

$questions_group = $db->get("questions_groups", [
    "order" => "id ASC"
]);


$files = $db->get("online_files", [
    "order" => "id ASC"
]);

$link_files = $db->get("online_files_link_forms");


foreach ($data as $key => $form) {
    $data[$key]["files"] = [];
    foreach ($files as $key2 => $file) {
        foreach ($link_files as $key3 => $link_file) {
            if ($link_file["id_form"] == $form["id"] && $link_file["id_file"] == $file["id"]) {
                $data[$key]["files"][] = $file;
            }
        }
    }
}
$data = filterNoPermit($data);
//$link_groups = $db->get("questions_link_online_files");


new ViewController("forms", ["create", "get"]);
new ViewController("online_files", ["create", "get", "getWhere"]);
new ViewController("online_files_link_forms", ["get", "create"]);
new ViewController("questions", ["create", "getWhere", "update"]);
new ViewController("questions_link_online_files", ["create", "getWhere"]);
new ViewController("questions_groups_link_online_files", ["get", "create", "getWhere"]);
new ViewController("questions_groups", ["get", "getWhere"]);




RoutesService::get_template_part("header.php", [
    "title" => "<i style='font-size: 25px;
        margin-bottom: 6px;
        margin-right: 5px;' 
        class='pi pi-file'></i> Formats",
]);

?>

<div class="justify-content-center align-items-center container-loader bg-light top-0 position-fixed loader-before-vue w-100 h-100"
    style="z-index:1; display:flex;">
    <div class="lds-ripple ">
        <div></div>
        <div></div>
    </div>
</div>
<div id="app-ui-formats" class="p-4 app-forms-se">
    <p-dialog v-model:visible="dialog_details" position="center" :modal="true" :closable="true" :resizable="false"
        :draggable="true">
        <template #header>
            <h4><i class="pi pi-file me-2"></i> Form Details: {{nameshort(format_selected.name)}} </h4>
        </template>
        <div class="edit-pdf">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="form-grid-2">
                            <div class="container-input-pdf field-expanded">
                                <label for="name">Name</label>
                                <p-inputtext id="name" v-model="format_selected.name" type="text" placeholder="Name"
                                    disabled>
                                </p-inputtext>
                            </div>

                            <div class=" container-input-pdf field-expanded">
                                <label for="description">Description</label>
                                <p-textarea id="description" v-model="format_selected.description" type="text" disabled
                                    placeholder="Description">
                                </p-textarea>
                            </div>
                            <div class=" container-input-pdf field-expanded">
                                <p-tree @node-select="viewFile" :value="files_online" node-key="id" expand-mode="all"
                                    :selection-mode="'single'" selection-keys="selected_file"
                                    :selection-change="viewFile">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <template #footer>
            <div class="p-d-flex p-jc-between">
                <p-button label="Close" icon="pi pi-times"
                    class="p-button p-component p-button-sm p-button-danger p-ml-auto p-button-rounded"
                    @click="dialog_details=false">
                </p-button>
            </div>
        </template>
    </p-dialog>

    <p-dialog contentClass="question-dialog-group" v-model:visible="dialog_questions_group" position="center"
        :modal="true" :closable="true" :resizable="false" :draggable="true">
        <template #header>
            <h4><i class="pi pi-question-circle me-2"></i>Edit Question Groups</h4>
        </template>
        <div class="questions-pdf">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div>
                            <div class=" container-input-pdf field-expanded">
                                <div class="panel-creation-question">
                                    <form class="question-form tabform-group form-grid-2">

                                        <div class="container-input-pdf field-expanded">
                                            <label for="group_questions">Select a Group</label>
                                            <p-dropdown v-model="group_questions.selected" id="group_questions"
                                                :options="group_questions.options" option-label="name"
                                                option-value="name" :editable="true">
                                            </p-dropdown>

                                        </div>
                                        <div class="container-input-pdf field-expanded">
                                            <label for="name">Question</label>
                                            <p-textarea id="question" placeholder="Write a Question" required
                                                name="question">
                                            </p-textarea>
                                        </div>

                                        <div class="container-input-pdf field-expanded" style="height:100px;"> <label
                                                for="info"> <i class="pi pi-info-circle me-2"></i>Info</label>
                                            <p-textarea id="info" placeholder="Write a field description" name="info">
                                            </p-textarea>
                                        </div>



                                        <div class="container-input-pdf">
                                            <label for="name">Type</label>
                                            <p-dropdown id="type" placeholder="Select a Type" optionLabel="name"
                                                optionValue="id" :options="type_selected" v-model="type_selection">
                                            </p-dropdown>
                                        </div>



                                        <div class="container-input-pdf field-expanded" v-if="
                                                    type_selection == 'select'|| 
                                                    type_selection == 'radio' ||
                                                    type_selection == 'checkbox' 
                                                ">

                                            <label for="name">Options</label>
                                            <p-textarea id="options"
                                                placeholder="Separate each option with a line break" type="text"
                                                name="options">
                                            </p-textarea>
                                        </div>



                                        <div class="container-input-pdf">
                                            <label for="name">Placeholder</label>
                                            <p-inputtext id="placeholder" placeholder="Placeholder" type="text">
                                            </p-inputtext>
                                        </div>

                                        <div class="container-input-pdf ">
                                            <label for="class">Class</label>
                                            <p-inputtext id="class" placeholder="Class" type="text">
                                            </p-inputtext>
                                        </div>



                                        <div class="container-input-pdf">

                                            <label for="name">Depend field</label>
                                            <p-dropdown id="depend_field_group" placeholder="Select a Question"
                                                option-label="name" option-value="value"
                                                :options="depend_field_group.options"
                                                v-model="depend_field_group.selected">
                                            </p-dropdown>
                                        </div>

                                        <div>
                                            <div class="container-input-pdf"
                                                v-if="depend_field_group.selected && depend_field_group.responses.options.length > 0">
                                                <label for="name">Show when the value is equal to any of these
                                                    values</label>

                                                <p-multiselect id="options_depend" placeholder="Select a Answer"
                                                    :options="depend_field_group.responses.options"
                                                    v-model="depend_field_group.responses.selected">
                                                </p-multiselect>

                                            </div>
                                        </div>


                                        <p-button label="Add Question" icon="pi pi-plus"
                                            class="mt-3 p-button p-component field-expanded p-button-sm p-button-success p-ml-auto p-button-rounded"
                                            @click="addQuestionWithGroup">
                                        </p-button>
                                    </form>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <template #footer>
            <div class="p-d-flex p-jc-between">

                <p-button label="Close" icon="pi pi-times"
                    class="p-button p-component p-button-sm p-button-danger p-ml-auto p-button-rounded"
                    @click="dialog_questions=false">
                </p-button>

            </div>
        </template>
    </p-dialog>

    <p-dialog contentClass="question-dialog" v-model:visible="dialog_questions" position="center" :modal="true"
        :closable="true" :resizable="false" :draggable="true">
        <template #header>
            <h4><i class="pi pi-question-circle me-2"></i>Questions for: {{nameshort(format_selected.name)}}
        </template>
        <div class="questions-pdf">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div>
                            <div class=" container-input-pdf field-expanded">
                                <p-tabview v-model:activeIndex="activeIndexTabView" scrollable
                                    @tab-change="changeIndexTab">

                                    <p-tabpanel v-tooltip="'Details'" v-for="(files, index) in format_selected.files"
                                        :key="files.name" :header="files.name">

                                        <div class="panel-creation-question">

                                            <div class="container-input-pdf field-expanded mt-4 mb-0">

                                                <label for="checked_field_groups">Select Groups
                                                    Question</label>

                                                <p-multiselect id="groups_for_file" placeholder="Select Groups"
                                                    option-label="name" option-value="id"
                                                    :options="groups_for_file.options"
                                                    @change="changeGroupForFileSelected"
                                                    v-model="groups_for_file.selected">
                                                </p-multiselect>

                                                <div class="separator-ui"></div>
                                            </div>
                                            <form :class="`question-form tabform-${index}`" class="form-grid-2">

                                                <div class="container-input-pdf field-expanded">
                                                    <label for="name">Question</label>
                                                    <p-textarea id="question" placeholder="Write a Question" required
                                                        name="question">
                                                    </p-textarea>
                                                </div>

                                                <div class="container-input-pdf field-expanded" style="height:100px;">
                                                    <label for="info"> <i
                                                            class="pi pi-info-circle me-2"></i>Info</label>
                                                    <p-textarea id="info" placeholder="Write a field description"
                                                        name="info">
                                                    </p-textarea>
                                                </div>

                                                <div
                                                    class="pb-3 d-flex align-items-center field-checkbox field-expanded">
                                                    <p-checkbox v-model="required" :binary="true" value="required"
                                                        name="required_field" inputId="required_field" class="me-2">
                                                    </p-checkbox>
                                                    <label for="required_field"
                                                        @click="required = !required">Required</label>
                                                </div>
                                                <div class="container-input-pdf ">
                                                    <label for="name">Type</label>
                                                    <p-dropdown id="type" placeholder="Select a Type" optionLabel="name"
                                                        optionValue="id" :options="type_selected"
                                                        v-model="type_selection">
                                                    </p-dropdown>
                                                </div>



                                                <div class="container-input-pdf field-expanded " v-if="
                                                    type_selection == 'select'|| 
                                                    type_selection == 'radio' ||
                                                    type_selection == 'checkbox' 
                                                ">

                                                    <label for="name">Options</label>
                                                    <p-textarea id="options"
                                                        placeholder="Separate each option with a line break" type="text"
                                                        name="options">
                                                    </p-textarea>
                                                </div>



                                                <div class="container-input-pdf">
                                                    <label for="name">Placeholder</label>
                                                    <p-inputtext id="placeholder" placeholder="Placeholder" type="text">
                                                    </p-inputtext>
                                                </div>

                                                <div class="container-input-pdf ">
                                                    <label for="class">Class</label>
                                                    <p-inputtext id="class" placeholder="Class" type="text">
                                                    </p-inputtext>
                                                </div>


                                                <div class="container-input-pdf">
                                                    <label for=" name">Depend field</label>
                                                    <p-dropdown id="question_for_this_format"
                                                        placeholder="Select a Question" option-label="name"
                                                        option-value="id" :options="question_for_this_format"
                                                        v-model="question_depend">
                                                    </p-dropdown>
                                                </div>

                                                <div>
                                                    <div class="container-input-pdf"
                                                        v-if="question_depend && options_depend.length > 0">
                                                        <label for="name">Show when the value is equal to any of these
                                                            values</label>

                                                        <p-multiselect id="options_depend" placeholder="Select a Answer"
                                                            optionLabel="name" optionValue="id"
                                                            :options="options_depend" v-model="options_depend_selected">
                                                        </p-multiselect>

                                                    </div>
                                                </div>


                                                <p-button label="Add Question" icon="pi pi-plus"
                                                    class="mt-3 field-expanded p-button p-component p-button-sm p-button-success p-ml-auto p-button-rounded"
                                                    @click="addQuestion">
                                                </p-button>





                                            </form>
                                        </div>


                                    </p-tabpanel>

                                </p-tabview>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <template #footer>
            <div class="p-d-flex p-jc-between">
                <p-button label="Preview" icon="pi pi-eye"
                    class="p-button p-component p-button-sm p-button-success p-ml-auto p-button-rounded"
                    @click="goPreview"></p-button>
                <p-button label="Close" icon="pi pi-times"
                    class="p-button p-component p-button-sm p-button-danger p-ml-auto p-button-rounded"
                    @click="dialog_questions=false">
                </p-button>

            </div>
        </template>
    </p-dialog>

    <section class="container-fluid">
        <div class="row p-datatable formats-online">
            <div class="header d-flex align-items-center p-datatable-header align-items-center">
                <div
                    class="controls w-100 d-flex flex-md-row flex-column align-items-center justify-content-md-between justify-content-center">
                    <div class="d-flex flex-column justify-content-center align-items-start">
                        <span class="p-input-icon-left">
                            <i class="pi pi-search"></i>
                            <p-inputtext placeholder="Keyword Search" @change="searchFormat"></p-inputtext>
                        </span>
                    </div>
                    <div class="box-buttons mt-md-0 mt-2 d-flex align-items-center " style="height:50px;">
                        <p-button icon="pi pi-plus" v-if="permissions == 'admin'"
                            class="p-button-sm p-button-rounded button-add me-3 p-button-success"
                            @click="dialog_questions_group=true" label="Edit Question Groups" style="line-height:1;">

                        </p-button>
                        <p-button icon="pi pi-refresh" class="p-button-sm  p-button-rounded button-sync" @click="sync">
                        </p-button>
                    </div>
                </div>
            </div>
            <i>Results: {{total_formats ? total_formats : 0}} Files: {{total_files ? total_files : 0}}</i>
            <div v-show="load_local_forms==false" class="row px-0 mx-0">
                <div v-show="formats_sync_loading">
                    Loading Formats...
                </div>
                <div class="col-12 col-md-6 col-lg-3 d-flex flex-column align-items-center py-3 px-2"
                    v-for="(format, key) in formats">
                    <div :class="`card p-0 w-100`"
                        style="height:250px;border: none;box-shadow: rgb(205 205 205) 2px 2px 6px 0px">
                        <div class="card-header d-flex">
                            <svg style="min-height:40px;min-width:40px;" height="40px" width="40px" version="1.1"
                                id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 309.267 309.267"
                                xml:space="preserve">
                                <g>
                                    <path style="fill:#E2574C;" d="M38.658,0h164.23l87.049,86.711v203.227c0,10.679-8.659,19.329-19.329,19.329H38.658
                            c-10.67,0-19.329-8.65-19.329-19.329V19.329C19.329,8.65,27.989,0,38.658,0z" />
                                    <path style="fill:#B53629;"
                                        d="M289.658,86.981h-67.372c-10.67,0-19.329-8.659-19.329-19.329V0.193L289.658,86.981z" />
                                    <path style="fill:#FFFFFF;" d="M217.434,146.544c3.238,0,4.823-2.822,4.823-5.557c0-2.832-1.653-5.567-4.823-5.567h-18.44
                            c-3.605,0-5.615,2.986-5.615,6.282v45.317c0,4.04,2.3,6.282,5.412,6.282c3.093,0,5.403-2.242,5.403-6.282v-12.438h11.153
                            c3.46,0,5.19-2.832,5.19-5.644c0-2.754-1.73-5.49-5.19-5.49h-11.153v-16.903C204.194,146.544,217.434,146.544,217.434,146.544z
                            M155.107,135.42h-13.492c-3.663,0-6.263,2.513-6.263,6.243v45.395c0,4.629,3.74,6.079,6.417,6.079h14.159
                            c16.758,0,27.824-11.027,27.824-28.047C183.743,147.095,173.325,135.42,155.107,135.42z M155.755,181.946h-8.225v-35.334h7.413
                            c11.221,0,16.101,7.529,16.101,17.918C171.044,174.253,166.25,181.946,155.755,181.946z M106.33,135.42H92.964
                            c-3.779,0-5.886,2.493-5.886,6.282v45.317c0,4.04,2.416,6.282,5.663,6.282s5.663-2.242,5.663-6.282v-13.231h8.379
                            c10.341,0,18.875-7.326,18.875-19.107C125.659,143.152,117.425,135.42,106.33,135.42z M106.108,163.158h-7.703v-17.097h7.703
                            c4.755,0,7.78,3.711,7.78,8.553C113.878,159.447,110.863,163.158,106.108,163.158z" />
                                </g>
                            </svg>
                            <h5 class="card-title"></h5>
                            <h4 class="card-subtitle mb-0 ms-2 d-flex align-items-center">{{nameshort(format.name)}}
                            </h4>
                        </div>
                        <div class="card-body">
                            <i v-show="format.status == 'error'" v-tooltip.bottom="format.message"
                                class="pi pi-exclamation-triangle position-absolute animation-fade"
                                style="color: #E2574C; font-size: 25px; right:10px;top:10px;"></i>

                            <i v-show="format.status == 'success'" v-tooltip.bottom="format.message"
                                class="pi pi-check-circle position-absolute animation-fade"
                                style="color: #4CAF50; font-size: 25px; right:10px;top:10px;"></i>
                            <p class="mb-0 scrollbar-0" style="max-height:11ex;">{{format.name}}</p>
                        </div>

                        <div class="card-footer">
                            <div class="panel-button d-flex justify-content-start align-items-center align-self-end">
                                <p-button icon="pi pi-question-circle" v-if="permissions == 'admin'"
                                    class="p-button-sm  p-button-rounded p-button-primary me-2" label="Questions"
                                    @click="openDialogQuestions(format)"
                                    v-tooltip.bottom="'Create questions for this form'">
                                </p-button>
                                <p-button icon="pi pi-info-circle" v-tooltip.bottom="'Details'"
                                    class="p-button-sm  p-button-rounded" @click="openDialogInfo(format)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="load_local_forms" class="row px-0 mx-0">
                <div class="skeleton d-flex justify-content-center col-12 col-md-6 col-lg-3" v-for="i in 12" :key="i">
                    <div class="card p-2 w-100" style="height:250px">
                        <div class=" d-flex gap-2 align-items-center">
                            <p-skeleton width="30%" height="70px"></p-skeleton>
                            <p-skeleton width="70%" height="30px"></p-skeleton>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-evenly">
                            <p-skeleton width="70%" height="10px"></p-skeleton>
                            <p-skeleton width="90%" height="20px"></p-skeleton>
                            <p-skeleton width="80%" height="15px"></p-skeleton>
                        </div>
                        <div class="w-100">
                            <p-skeleton width="100%" height="40px"></p-skeleton>
                        </div>
                    </div>
                </div>


            </div>
    </section>
</div>

<style>
.container-pdf {
    border-radius: 20px;
    border: 1px solid #e8e8e8;
    cursor: pointer;
    background: #f3f3f3;
    color: #525252;
    font-size: 16px;
    opacity: 0.85;
    transition: all 0.3s ease;
}

.container-pdf h5 {
    font-size: 16px;
}

.p-dialog-mask {
    z-index: 999900 !important;
}

.container-pdf:hover {
    opacity: 1;
}

.p-button.p-button-sm .p-button-icon {
    font-size: 1rem;
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const {
        createApp,
        ref,
        reactive,
        computed,
        watchEffect,
        onMounted
    } = Vue

    const app = {
        components: {
            "p-inputtext": primevue.inputtext,
            "p-button": primevue.button,
            "p-dialog": primevue.dialog,
            "p-textarea": primevue.textarea,
            "p-dropdown": primevue.dropdown,
            "p-tree": primevue.tree,
            "p-spinner": primevue.progressspinner,
            "p-skeleton": primevue.skeleton,
            "p-tabview": primevue.tabview,
            "p-tabpanel": primevue.tabpanel,
            "p-checkbox": primevue.checkbox,
            "p-multiselect": primevue.multiselect,
        },
        methods: {
            goPreview() {
                $url = "<?= site_url() ?>/online_forms_inmigration?id=" + this
                    .format_selected.files[this.activeIndexTabView].id;
                window.open($url, '_blank');
            },
            nameshort(name) {
                if (name) {

                    let e = name.substring(0, name.indexOf("|"));
                    return e.trim();
                }
                return name;
            },
            async selectFormat(format) {
                this.question_for_this_format = [];
                this.question_id_for_this_format = [];
                this.question_depend = "";
                this.options_depend = [];
                this.list_options_depend_JSON = [];
                this.format_selected = format;
                this.files_online = [{
                    id: 1,
                    label: 'Files',
                    children: [],
                    selectable: false,
                    expanded: true,
                    icon: 'pi pi-folder'
                }];

                this.groups_for_file.options = await QuestionsGroups.get().then((res) => {
                    return res;
                }).catch((err) => {
                    console.log(err);
                });

                this.groups_for_file.selected = [];



                for (let i = 0; i < format.files.length; i++) {
                    this.files_online[0].children.push({
                        "id": i,
                        "icon": "pi pi-file",
                        "label": format.files[i].name,
                        "selectable": true,
                        "url": format.files[i].url
                    });
                }

                fetch("<?= RoutesService::get_api_base(); ?>getQuestionsGroupsByIdFile/" +
                    this
                    .format_selected.files[this.activeIndexTabView].id, {
                        method: "GET",
                        headers: {
                            "Content-Type": "application/json"
                        },
                    }).then(async (res) => {
                    let groups = await res.json();
                    if (groups != "empty") {
                        let new_g = [];
                        for (let i = 0; i < groups.length; i++) {
                            new_g.push(groups[i].id);
                        }
                        console.log(this.groups_for_file.selected);
                        console.log(new_g);
                        this.groups_for_file.selected = new_g;
                    }
                }).catch((err) => {
                    console.log(err);
                });

                QuestionsLinkOnlineFiles.getWhere({
                    "id_file": format.files[this.activeIndexTabView].id
                }).then(async (res) => {
                    if (res != "empty" && res) {
                        for (let i = 0; i < res.length; i++) {
                            Questions.getWhere({
                                "id": res[i].id_question
                            }).then((res2) => {
                                if (res2 != "empty") {
                                    this.question_for_this_format.push({
                                        name: res2[0]["question"],
                                        id: res2[0]["id"]
                                    });
                                    this.question_id_for_this_format.push(res2[0][
                                        "id"
                                    ]);
                                    let options = JSON.parse(res2[0]["options"])
                                        .options;
                                    this.list_options_depend_JSON.push(options);
                                }
                            }).catch((err) => {
                                console.log(err);
                            });
                        }
                    }
                }).catch((err) => {
                    console.log(err);
                });




            },
            cleanQuestionForm($tab = null) {
                $tab = $tab == null ? this.activeIndexTabView : $tab;
                let form = document.querySelector(".tabform-" + $tab);
                form.querySelector("#question").value = "";
                this.required = false;
                if (form.querySelector("#options")) {
                    form.querySelector("#options").value = "";
                }
                if (form.querySelector("#placeholder")) {
                    form.querySelector("#placeholder").value = "";
                }
                if (form.querySelector("#info")) {
                    form.querySelector("#info").value = "";
                }
                if (form.querySelector("#class")) {
                    form.querySelector("#class").value = "";
                }

                this.question_for_this_format = [];
                this.question_id_for_this_format = [];
                this.options_depend = [];

                this.selectFormat(this.format_selected);

            },
            addQuestion($evt, $tab = null, $files_link = true) {
                $tab_ = $tab == null ? this.activeIndexTabView : $tab;

                let form = document.querySelector(".tabform-" + $tab_);
                let info = form.querySelector("#info") ? form.querySelector("#info").value : "";
                let methods = {
                    "only_show": this.question_depend,
                    "value_is": this.options_depend_selected
                }

                let options = {
                    methods,
                    "options": form.querySelector("#options") ? form.querySelector(
                        "#options").value.split("\n") : []
                }

                if ($tab == "group") {
                    methods = {
                        "only_show": this.depend_field_group.selected,
                        "value_is": this.depend_field_group.responses.selected
                    }
                    options.methods = methods;
                }
                let data = {
                    "question": form.querySelector("#question").value,
                    "type": this.type_selection,
                    "info": info,
                    "placeholder": form.querySelector("#placeholder") ? form.querySelector(
                        "#placeholder").value : "",
                    "class": form.querySelector("#class") ? form.querySelector("#class").value : "",
                    "options": JSON.stringify(options)
                }
                console.log(data);

                if (form.checkValidity()) {
                    return Questions.create(data).then((res) => {

                        /*  if (res) {
                          let name_pdf_field = "input_" + res + "_" + Math.floor(Math.random() *
                                10000);

                            Questions.update({
                                name_pdf_field: name_pdf_field
                            }, res);

                        }*/

                        if ($files_link) {
                            let id = res;
                            let files = this.format_selected.files;
                            // console.log(this.activeIndexTabView, files);
                            let id_file = files[this.activeIndexTabView].id;
                            return QuestionsLinkOnlineFiles.getWhere({
                                id_file
                            }).then((res) => {
                                let order = res == "empty" ? 1 : res.length + 1;
                                return QuestionsLinkOnlineFiles.create({
                                    id_file: id_file,
                                    id_question: id,
                                    order_question: order,
                                    required: this.required
                                }).then((res) => {
                                    this.cleanQuestionForm($tab);
                                    return id;
                                }).catch((err) => {
                                    console.log(err);
                                    return false;
                                });

                            }).catch((err) => {
                                console.log(err);
                                return false;
                            });
                        } else {
                            return res;
                        }

                    }).catch(function(err) {
                        console.log(err);
                        return false;
                    });
                } else {
                    form.reportValidity();
                    return false;
                }
            },
            cleanQuestionGroupDialog() {
                let form = document.querySelector(".tabform-group");
                form.querySelector("#question").value = "";
                this.required = false;
                if (form.querySelector("#options")) {
                    form.querySelector("#options").value = "";
                }
                if (form.querySelector("#placeholder")) {
                    form.querySelector("#placeholder").value = "";
                }


                this.options_depend_groups = [];
                let last = this.group_questions.selected;
                this.group_questions.selected = "";
                this.group_questions.selected = last;
            },
            async addQuestionWithGroup() {
                let group = this.group_questions.selected;
                let options = this.depend_field_group;

                if (group == "" || !group) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'You must select or create a group',
                    });
                } else {
                    let id = await this.addQuestion(null, "group", false);
                    if (id) {
                        let res = await fetch(
                            "<?= RoutesService::get_api_base() ?>setQuestionInGroup", {
                                method: "POST",
                                body: JSON.stringify({
                                    group,
                                    id_question: id
                                }),
                                headers: {
                                    "Content-Type": "application/json"
                                }
                            });
                        if (res) {
                            this.cleanQuestionGroupDialog();
                        }
                    }

                }

            },

            openDialogInfo: function(format) {
                this.dialog_details = true;
                this.selectFormat(format);

            },
            openDialogQuestions: function(format) {
                this.dialog_questions = true;
                //this.activeIndexTabView.value = 0;
                this.selectFormat(format);
            },
            viewFile(event) {
                window.open(event.url, '_blank');
            },
            searchFormat: function() {},
            downloadFormat: function() {
                let format = this.format_selected;
                let name = format.file;
                //si terminar en .pdf quitar
                if (name.endsWith(".pdf")) {
                    name = name.substring(0, name.length - 4);
                }

                fetch("<?= RoutesService::get_api_base(); ?>download/pdf" + "?src=" + name)
                    .then(response => response.blob()).catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                    })
            },
            changeGroupForFileSelected(evt) {
                let val = this.groups_for_file.selected;
                if (val) {
                    let id_file_active = this.format_selected.files[this.activeIndexTabView].id;
                    let data = {
                        id_file: id_file_active,
                        groups: val
                    }
                    fetch("<?= RoutesService::get_api_base(); ?>setGroupInOnlineFile", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data)
                    }).then(response => response.json()).then(data => {
                        console.log("se actualizo el group", data);
                    }).catch(error => {
                        console.log(error);
                    })
                }
            },
            sync() {

                if (!this.status_sync) {
                    this.status_sync = true;
                    let iconsync = document.querySelector(".button-sync .pi-refresh");
                    iconsync.classList.add("pi-spin");
                    fetch("<?= RoutesService::get_api_base(); ?>inmigration_forms/sync")
                        .then(response => response.json()).then(data => {
                            if (data.status == "200") {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sync',
                                    text: 'Sync completed successfully!'
                                })
                                if (data.data.length > 0) {
                                    this.formats.push(...data.data);
                                }
                            } else {
                                console.log("error", data);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!'
                                })
                            }
                        }).catch(error => {
                            console.log(error, "error2");
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!'
                            })
                        }).finally(() => {
                            iconsync.classList.remove("pi-spin");
                            this.status_sync = false;
                        })
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Sync in progress!'
                    })
                }
            },
            changeIndexTab(event) {

                this.activeIndexTabView = event.index;

                //this.cleanQuestionForm();
            },

        },
        watch: {
            question_depend: {
                handler: function(val, oldVal) {
                    let id_question = this.question_id_for_this_format;
                    let key_question = this.question_for_this_format.findIndex(
                        (item) => item.id == val);
                    if (key_question != -1) {
                        let all_options = this.list_options_depend_JSON[key_question];


                        this.options_depend = all_options;
                    }
                },
                deep: true
            },
            group_questions: {
                handler: function(val, oldVal) {
                    if (val.selected != "") {
                        let new_options = [];
                        let time = setTimeout(() => {
                            clearTimeout(time);

                            fetch("<?= RoutesService::get_api_base(); ?>getQuestionsByNameGroup/" +
                                val.selected, {
                                    method: "GET",
                                    headers: {
                                        "Content-Type": "application/json"
                                    }
                                }).then(response => response.json()).then(data => {
                                if (data && data != "empty") {
                                    let questions = data;

                                    questions.forEach(question => {
                                        if (question.question.trim() == "") {
                                            question.question =
                                                "No question name";
                                        }
                                        let option = {
                                            name: question.question,
                                            value: question.id,
                                            type: question.type
                                        }
                                        new_options.push(option);
                                    });
                                    this.depend_field_group.options =
                                        new_options;
                                }
                            }).catch(error => {
                                console.log(error);
                            }).finally(() => {
                                clearTimeout(time);
                            })

                            clearTimeout(time);
                        }, 1500);
                    }
                },
                deep: true
            },
            "depend_field_group.selected": {
                handler: function(val, oldVal) {

                    if (val.selected != "") {
                        this.depend_field_group.responses.options = [];
                        let new_options = [];
                        let time = setTimeout(() => {
                            let questions = val;
                            fetch("<?= RoutesService::get_api_base(); ?>getOptionsByQuestionId/" +
                                val, {
                                    method: "GET",
                                    headers: {
                                        "Content-Type": "application/json"
                                    }
                                }).then(response => response.json()).then(data => {
                                if (data && data != "empty") {


                                    let options = data;
                                    for (let i = 0; i < options.length; i++) {
                                        if (options[i].options != "") {
                                            options[i].options = JSON.parse(options[i]
                                                .options);

                                            for (let j = 0; j < options[i].options
                                                .options.length; j++) {

                                                this.depend_field_group.responses
                                                    .options.push(options[i].options
                                                        .options[j]);
                                            }
                                        }
                                    }
                                }
                            }).catch(error => {
                                console.log(error);
                            }).finally(() => {
                                clearTimeout(time);
                            })

                            clearTimeout(time);
                        }, 1000);

                    }
                },
                deep: true
            }
        },
        setup() {
            const formats = ref(<?= json_encode($data) ?>);
            const dialog_details = ref(false);
            const dialog_questions = ref(false);
            const format_selected = ref({});
            const formats_online = ref([]);
            const load_local_forms = ref(false);
            const required = ref([]);
            const total_formats = computed(() => formats.value.length);
            const total_files = computed(() => {
                let files = [];
                for (let i = 0; i < formats.value.length; i++) {
                    total_files.value += formats.value[i].files.length;

                    for (let a = 0; a < formats.value[i].files.length; a++) {
                        if (files.includes(formats.value[i].files[a].url)) {
                            continue;
                        } else {
                            files.push(formats.value[i].files[a].url);
                        }
                    }
                }
                return files.length;
            });
            const formats_sync_loading = ref(false);
            const depend = ref([]);
            const question_id_for_this_format = reactive([]);
            const question_for_this_format = reactive([]);
            const question_depend = ref("");
            const options_depend = reactive([]);
            const options_depend_selected = ref([]);
            const list_options_depend_JSON = reactive("");
            const status_sync = ref(false);
            const dialog_questions_group = ref(false);
            const groups_for_file = ref({
                selected: [],
                options: []
            });
            const permissions = "no-edit";

            let f = <?= json_encode($questions_group) ?>;
            //obtener solo los id

            const group_questions = ref({
                selected: "",
                options: f
            });
            const depend_field_group = ref({
                selected: "",
                options: [],
                responses: {
                    options: [],
                    selected: ""
                }
            });






            const activeIndexTabView = ref(0);
            const type_selected = ref([
                "text",
                "textarea",
                "select",
                "checkbox",
                "radio",
                "date",
                "number",
                "email"
            ]);
            const type_selection = ref("text");
            const files_online = ref([{
                id: 1,
                label: 'Files',
                children: []
            }]);

            const all_keys = files_online.value.map(node => node.id);
            const expandedKeys = ref(all_keys);

            onMounted(() => {
                let loader = document.querySelector(".loader-before-vue");
                loader.style.transition = "all 0.5s";
                loader.style.opacity = "0";
                setTimeout(() => {
                    loader.style.display = "none";
                }, 500);
            });

            return {
                files_online,
                formats,
                dialog_details,
                format_selected,
                formats_online,
                expandedKeys,
                load_local_forms,
                dialog_questions,
                type_selected,
                type_selection,
                required,
                total_formats,
                total_files,
                activeIndexTabView,
                formats_sync_loading,
                depend,
                question_for_this_format,
                question_id_for_this_format,
                question_depend,
                options_depend,
                options_depend_selected,
                list_options_depend_JSON,
                status_sync,
                dialog_questions_group,
                group_questions,
                depend_field_group,
                groups_for_file,
                permissions

            }

        }
    }
    createApp(app).use(primevue.config.default).directive('tooltip', primevue.tooltip).mount("#app-ui-formats");
});
</script>
<style>
.edit-pdf,
.questions-pdf {
    height: 100%;
    width: 90vw;
    max-width: 650px;
}

.p-dropdown-panel {
    z-index: 5555555 !important;
}

.swal2-container {
    z-index: 5000000;
}

.container-input-pdf {
    width: 100%;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.separator-ui {
    width: 100%;
    margin: 30px 0;
    height: 1px;
    z-index: 5545456454654654564;
    position: relative;
    visibility: visible;
    display: block;
    opacity: 0.8;
    background: #e1e1e1;
}

.p-dialog-content {
    overflow-x: hidden;
}

.container-input-pdf label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
}

.container-input-pdf p-inputtext {
    width: 100%;
}

.container-input-pdf p-textarea {
    width: 100%;
}



.animation-fade {
    animation: temblor-con-rotacion 0.3s;
}

:disabled {
    color: black !important;
}

.p-tooltip {
    z-index: 2599999 !important;
}

.p-multiselect-panel {
    z-index: 5000000 !important;
}

/*
.enable {
    opacity: 0.8;
}*/

@media (max-width: 575px) {
    .p-dialog {
        width: 95%;
    }

    .edit-pdf,
    .questions-pdf {
        width: 100%;
    }
}
</style>
<?php
//obtener instancia de wpdb

$db = new DatabaseController();
global $key_pluging;


$data = $db->get("applications", [
    "select" => "
    wp_$key_pluging" . "_applications.*,
    wp_$key_pluging" . "_clients.id as id_client,
    wp_$key_pluging" . "_clients.name,
    wp_$key_pluging" . "_clients.email,
    wp_$key_pluging" . "_clients.phone_1,
    wp_$key_pluging" . "_clients.phone_2,
    wp_$key_pluging" . "_clients.address",
    "join" => "JOIN wp_$key_pluging" . "_clients ON wp_$key_pluging" . "_clients.id = wp_$key_pluging" . "_applications.id_client",
    "order" => "wp_$key_pluging" . "_applications.id DESC",
]);



if ($data == "empty" || !$data) {
    $data = [];
}

$clients = $db->get("clients", [
    "select" => "id, name",
    "order" => "id DESC"
]);

$clients = $clients && $clients != "empty" ? $clients : [];

$fomats = $db->get("forms", [
    "select" => "id, name",
    "order" => "id ASC"
]);

$fomats = $fomats && $fomats != "empty" ? $fomats : [];

$files_link_forms = $db->get("online_files_link_forms", [
    "select" => "id_form, id_file"
]);



$files_link_forms = $db->linkData($files_link_forms, "id_form", "forms.name");
$files_link_forms = $db->linkData($files_link_forms, "id_file", "online_files.name");

$fomats = filterNoPermit($fomats);


new ViewController("applications", ["create", "get"]);
new ViewController("application_link_online_files", ["create"]);
new ViewController("online_files", ["get"]);

RoutesService::get_template_part("header.php", [
    "title" => "<i style='font-size: 25px;
        margin-bottom: 6px;
        margin-right: 5px;' 
        class='pi pi-file-o'></i>Clients Forms"
]);

?>
<div class="justify-content-center align-items-center container-loader bg-light top-0 position-fixed loader-before-vue w-100 h-100"
    style="z-index:1; display:flex;">
    <div class="lds-ripple ">
        <div></div>
        <div></div>
    </div>
</div>

<div id="app-ui-clients" class="p-md-4 p-2 app-forms-se">
    <p-dialog v-model:visible="dialog_create" position="center" :modal="true" :closable="true" :resizable="false"
        :draggable="true" @hide="updateForms" :dismissableMask="true">
        <template #header>
            <h4><i class="pi pi-plus"></i>
                Assemble your Smart Form</h4>
        </template>
        <div class="create-contact" style="width:90vw; max-width: 650px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <form class="form-grid form-create">
                            <div
                                class="container-input-pdf  py-3  d-flex justify-content-center align-items-start flex-column">
                                <label for="clients">Select a client</label>
                                <span class="p-input-icon-right w-100">
                                    <p-dropdown id="clients" placeholder="Select a Client" :options="clients_options"
                                        v-model="createform.client" class="w-100" option-label="name" option-value="id">
                                    </p-dropdown>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.client">{{validation_errors.client}}</span>
                            </div>
                            <div
                                class="container-input-pdf  py-3  d-flex justify-content-center align-items-start flex-column">
                                <label for="forms">Select all necessary forms</label>
                                <span class="p-input-icon-right w-100">
                                    <p-multiselect id="forms" placeholder="Select a Form" :options="formats_options"
                                        v-model="createform.forms" class="w-100" option-label="name" option-value="id"
                                        :filter="true">
                                    </p-multiselect>

                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.forms">{{validation_errors.forms}}</span>
                            </div>
                            <div v-for="(file, index) in createform.files" :key="index"
                                class="container-input-pdf  py-3  d-flex justify-content-center align-items-start flex-column">
                                <label for="name">Files {{ file.name }}</label>
                                <span class="p-input-icon-right w-100">
                                    <p-multiselect id="file" placeholder="Select a Form" :options="file.options"
                                        v-model="file.selected" class="w-100" option-label="name"
                                        option-value="id_file"> </p-multiselect>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.files">{{validation_errors?.files}}</span>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
            <div class="d-flex justify-content-end">

                <p-button label="Create Application" icon="pi pi-plus" style="height: 45px; font-size:15px;"
                    class=" d-flex align-items-center p-button p-component  p-button-success p-ml-auto p-button-rounded p-button-sm"
                    iconPos="right" @click="createApplication"></p-button>
                </p-button>
            </div>
        </template>
    </p-dialog>
    <p-datatable :value="forms" selectionMode="single" :paginator="true" :rows="10" :rowsPerPageOptions="[10,20,50]">
        <template #header>
            <div
                class="controls d-flex flex-md-row flex-column align-items-md-end align-items-center justify-content-md-between justify-content-center">
                <div class="d-flex flex-column justify-content-center align-items-start">
                    <span class="p-input-icon-left">
                        <i class="pi pi-search"></i>
                        <p-inputtext placeholder="Keyword Search"></p-inputtext>
                    </span>
                </div>
                <div class="box-buttons d-flex align-items-center mt-md-0 mt-2 " style="height:50px;">
                    <p-button label="Add" icon="pi pi-plus" @click="dialog_create=true"
                        class="me-2 p-button-sm p-button-success p-ml-auto  p-button-rounded">
                    </p-button>
                    <p-button icon="pi pi-refresh" class="p-button-sm  p-button-rounded">
                    </p-button>
                </div>
            </div>
        </template>
        <template #empty>
            No Clients data found.
        </template>
        <template #loading>
            Loading Forms data. Please wait.
        </template>
        <p-column field="Nr" header="Nr">
            <template #body="{data, index}">
                {{index + 1}}
            </template>
        </p-column>
        <p-column header="PDF"><template #body="{data, index}">

                <p-button icon="pi pi-file-pdf" class="p-button-rounded p-button-sm p-button-text  p-button-danger"
                    @click="openFolder(data.id)">
                </p-button>
            </template>
        </p-column>

        <p-column field="name" header="Client"></p-column>
        <!--<p-column field="matters" header="Matters"></p-column>-->
        <p-column field="status" header="Status">
            <template #body="{data}">
                <span class="p-tag p-tag-rounded" :class="{
                        'p-tag-success': data.status == 'Success',
                        'p-tag-warning': data.status == 'To Review', 
                        'p-tag-primary': data.status == 'To do'
                    }">{{data.status}}</span>
            </template>
        </p-column>
        <p-column field="updated_at" header="Update"></p-column>
        <p-column header="Open"><template #body="{data}">
                <p-button icon="pi pi-link" class="p-button-rounded p-button-sm p-button-text p-button-success"
                    @click="goForm(data.keyform)"></p-button>

            </template>
        </p-column>
        <p-column header="Share">
            <template #body="{data}">
                <p-button icon="pi pi-share-alt" class="p-button-rounded p-button-sm p-button-text p-button-info"
                    @click="shareForm(data.keyform)"></p-button>
            </template>
        </p-column>
    </p-datatable>


    <view-pdf :src="urlpdf" />
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const {
        createApp,
        ref,
        reactive,
        computed,
        computedAsync,
        watchEffect,
        onMounted,
    } = Vue
    const {
        FilterMatchMode,
        FilterOperator
    } = primevue.api;

    const app = {
        components: {
            "p-datatable": primevue.datatable,
            "p-column": primevue.column,
            "p-inputtext": primevue.inputtext,
            "p-button": primevue.button,
            "p-dialog": primevue.dialog,
            "p-dropdown": primevue.dropdown,
            "p-multiselect": primevue.multiselect,
            "view-pdf": embedPDF,
            "p-tieredmenu": primevue.tieredmenu
        },
        methods: {
            async openFolder(id) {
                let response = await fetch(
                    "<?= RoutesService::get_api_base() ?>getApplicationFilestByIdApplication/" +
                    id, {
                        method: "GET"
                    });
                if (response) {
                    response.json().then(data => {
                        if (data && data != "empty") {
                            this.urlpdf = data[0];
                            this.files_list = [];
                            //console.log("data", data);
                            data.forEach(element => {
                                let slug = element.split("/");
                                slug = slug[slug.length - 1];
                                this.files_list.push({
                                    label: slug,
                                    icon: "pi pi-file-pdf",
                                    command: () => {
                                        this.urlpdf = element;
                                    }
                                });

                            });

                        } else {
                            console.log("errors", data);
                        }
                    }).catch(error => {
                        console.log(error);
                    });
                } else {
                    console.log("error");
                }
            },
            goForm(keyform) {
                //abrir en nueva pestaña
                window.open("<?= site_url(); ?>/forms_inmigration?key=" + keyform, '_blank');
            },
            openDialogCreate() {
                this.dialog_create = true;
            },
            cleanApplicationForm() {
                this.createform = {
                    forms: [],
                    client: null
                }
            },
            updateForms(data) {
                fetch("<?= RoutesService::get_api_base() ?>getApplications", {
                    method: "GET",
                }).then(response => response.json()).then(data => {
                    if (data && data != "empty") {
                        this.forms = data;
                    }
                }).catch(error => {
                    console.log(error);
                });
            },
            createApplication() {
                if (this.status_create == "loading") return;
                this.status_create = "loading";
                let results = this.applyValidations(this.createform, this.createformvalidations);
                if (results.length > 0) {
                    for (let i = 0; i < results.length; i++) {
                        this.validation_errors[results[i].field] = results[i].message;
                    }
                    console.log(this.validation_errors);
                    return;
                } else {
                    //console.log(JSON.stringify(this.createform));
                    fetch("<?= RoutesService::get_api_base() ?>createApplication", {
                        method: "POST",
                        body: JSON.stringify(this.createform),
                    }).then(response => response.json()).then(res => {
                        if (res) {
                            console.log(res);
                            if (res == "error") {
                                Swal.fire({
                                    title: 'Application not created',
                                    text: errors.join(", "),
                                    icon: 'warning',
                                    confirmButtonText: 'Accept'
                                });
                                this.status_create = "error";
                                return;
                            }
                            if (Array.isArray(res)) {
                                let errors = [];
                                for (let i = 0; i < res.length; i++) {
                                    if (res[i].status == "error") {
                                        errors.push(res[i].slug);
                                    }
                                }
                                if (errors.length > 0) {
                                    Swal.fire({
                                        title: 'Consider that the following files were not added to the application',
                                        text: errors.join(", "),
                                        icon: 'warning',
                                        confirmButtonText: 'Accept'
                                    });
                                }
                            }
                            this.status_create = "success";
                            this.updateForms();
                            this.cleanApplicationForm();
                        } else {
                            this.status_create = "error";
                        }

                    }).catch(error => {
                        console.log('Error:', error);
                        this.status_create = "error";
                    });
                }
            },
            nameshort(name) {
                if (name) {
                    let i = name.substring(0, name.indexOf("|"));
                    return i.trim();
                }
                return name;
            },
            applyValidations(fields, validations) {
                let result_validations = [];
                for (const [key, value] of Object.entries(validations)) {
                    let field = fields[key] ? fields[key] : "";
                    let rules = value.split('|');


                    for (let i = 0; i < rules.length; i++) {
                        let rule = rules[i];
                        let ruleName = rule.split(':')[0];
                        let ruleValue = rule.split(':')[1];


                        switch (ruleName) {
                            case 'max-length':
                                if (field.length > 0) {
                                    if (field.length > ruleValue) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} is invalid`
                                        })
                                    }
                                }
                                break;
                            case 'min-length':
                                if (field.length > 0) {
                                    if (field.length < ruleValue) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} is invalid`
                                        })
                                    }
                                }
                                break;
                            case 'alpha':
                                if (field.length > 0) {
                                    if (!/^[a-zA-Z]*$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be only letters`
                                        })
                                    }
                                }
                                break;

                            case 'numeric':
                                if (field.length > 0) {
                                    if (!/^[0-9]*$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be only numbers`
                                        })
                                    }
                                }
                                break;
                            case 'email':
                                if (field.length > 0) {
                                    if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be a valid email`
                                        })
                                    }
                                }
                                break;
                            case 'alpha.space':
                                if (field.length > 0) {
                                    if (!/^[a-zA-Z ]*$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be only letters and spaces`
                                        })
                                    }
                                }
                                break;
                            case 'alphanumeric':
                                if (field.length > 0) {
                                    if (!/^[a-zA-Z0-9]*$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be only letters and numbers`
                                        })
                                    }
                                }
                                break;
                            case 'alphanumeric.space':
                                if (field.length > 0) {
                                    if (!/^[a-zA-Z0-9 ]*$/g.test(field)) {
                                        result_validations.push({
                                            field: key,
                                            message: `the field ${key} must be only letters, numbers and spaces`
                                        })
                                    }
                                }
                                break;
                            case 'required':
                                if (field == null || field == "" || field == [] || field == {}) {
                                    result_validations.push({
                                        field: key,
                                        message: `the field ${key} is required`
                                    })
                                }
                                break;
                            default:
                                break;

                        }
                    }
                }
                return result_validations;
            },
            onRowContextMenu(event) {
                this.$refs.cm.show(event.originalEvent);
            },
        },
        setup() {
            const forms = ref(<?= json_encode($data) ?>);
            const dialog_create = ref(false);
            const validation_errors = ref({});
            const clients_options = ref(<?= json_encode($clients) ?>);
            const formats_options = ref(<?= json_encode($fomats) ?>);
            const link_forms_files = ref(<?= json_encode($files_link_forms) ?>);
            const status_create = ref(null);
            const selecteditems = ref(null);
            const uploaddir = ref("<?= RoutesService::getupload() ?>");
            const urlpdf = ref("");
            const createform = ref({
                client: null,
                forms: [],
                files: {},
            });

            const createformvalidations = ref({
                client: "required",
                forms: "required",
                files: "required",
            });

            function nameshort(name) {
                if (name) {
                    let i = name.substring(0, name.indexOf("|"));
                    return i.trim().toUpperCase();
                }
                return name;
            }

            watchEffect(() => {
                let files = {};
                for (let i = 0; i < createform.value.forms.length; i++) {
                    const form = createform.value.forms[i];
                    let shortname = formats_options.value.find(x => x.id == form).name
                        .substring(0,
                            formats_options.value.find(x => x.id == form).name.indexOf("|")
                        );
                    let files_form = {};
                    for (let j = 0; j < link_forms_files.value.length; j++) {

                        const link_form_id = link_forms_files.value[j].id_form;

                        if (form == link_form_id) {
                            files_form = files_form ? files_form : {};
                            files_form.options = files_form?.options ?
                                files_form
                                .options : [];

                            let name_file = link_forms_files.value[j].online_files_name;
                            if (!name_file.toLowerCase().startsWith("instructions") &&
                                !name_file.toLowerCase().includes("instr")

                            ) {
                                //si el nombre del archivo corto esta forms_availables
                                shortname = nameshort(name_file);



                                files_form.name = shortname;
                                if (files_form.options.length == 0) {
                                    files_form.options.push({
                                        name: link_forms_files.value[j]
                                            .online_files_name,
                                        id_file: link_forms_files.value[j].id_file,
                                    });
                                    files_form.selected = [];
                                    files_form.selected.push(link_forms_files.value[j]
                                        .id_file);
                                } else {
                                    files_form.options.push({
                                        name: link_forms_files.value[j]
                                            .online_files_name,
                                        id_file: link_forms_files.value[j].id_file,
                                    });
                                }
                            }
                        }
                    }

                    files[form] = files_form;
                }
                createform.value.files = files;
            });

            onMounted(() => {
                let loader = document.querySelector(".loader-before-vue");
                loader.style.transition = "all 0.5s";
                loader.style.opacity = "0";
                setTimeout(() => {
                    loader.style.display = "none";
                }, 500);
            });



            return {
                forms,
                dialog_create,
                createform,
                createformvalidations,
                validation_errors,
                clients_options,
                formats_options,
                status_create,
                urlpdf,
                link_forms_files,
            }
        }
    }
    createApp(app).use(primevue.config.default).mount('#app-ui-clients')
});
</script>

<style>
@media (max-width: 782px) {
    td[role="cell"] {
        text-align: end !important;
    }

    .p-column-title {
        padding-right: 10px;
    }
}

.p-multiselect-panel,
.p-dropdown-panel {
    max-width: 450px !important;
}

.p-multiselect-label-container,
.p-dropdown-label-container {
    /*max-width: 300px !important;*/
}

.swal2-container {
    z-index: 6565;
}

.embed-pdf {
    height: 1100px;
    margin-top: 50px;
}
</style>
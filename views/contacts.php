<?php
//obtener instancia de wpdb
$db = new DatabaseController();
$data = $db->get("clients", [
    "order" => "id ASC"
]);

if ($data == "empty" || !$data) {
    $data = [];
}

RoutesService::get_template_part("header.php", [
    "title" => "<i style='font-size: 25px;
        margin-bottom: 6px;
        margin-right: 5px;' 
        class='pi pi-users'></i> Clients"
]);

new ViewController("clients", ["create"]);

?>
<div class="justify-content-center align-items-center container-loader bg-light top-0 position-fixed loader-before-vue w-100 h-100"
    style="z-index:1; display:flex;">
    <div class="lds-ripple ">
        <div></div>
        <div></div>
    </div>
</div>
<div id="app-ui-clients" class="p-4 app-forms-se">
    <p-dialog v-model:visible="dialog_create" position="center" :modal="true" :closable="true" :resizable="false"
        :draggable="true">
        <template #header>
            <h4><i class="pi pi-plus"></i>
                Create New Contact </h4>
        </template>
        <div class="create-contact" style="width:90vw; max-width: 650px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <form class="form-grid-2 form-create">
                            <div
                                class="container-input-pdf   py-3 d-flex justify-content-center align-items-start flex-column">

                                <span class="p-input-icon-right w-100">
                                    <p-inputtext id="name" type="text" placeholder="Name" class="d-flex w-100"
                                        v-model="createform.name" required>
                                    </p-inputtext>
                                    <i class="pi pi-user "></i>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.name">{{validation_errors?.name}}</span>
                            </div>
                            <div
                                class="container-input-pdf  py-3  d-flex justify-content-center align-items-start flex-column">
                                <span class="p-input-icon-right w-100">
                                    <p-inputtext id="email" type="text" placeholder="Email" class="d-flex w-100"
                                        v-model="createform.email" required>
                                    </p-inputtext>
                                    <i class="pi pi-envelope"></i>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.email">{{validation_errors?.email}}</span>
                            </div>
                            <div
                                class="container-input-pdf  py-3 d-flex justify-content-center align-items-start flex-column">
                                <span class="p-input-icon-right w-100">
                                    <p-inputtext id="phone_1" type="text" placeholder="Phone 1" class="d-flex w-100"
                                        v-model="createform['phone 1']" required>
                                    </p-inputtext>
                                    <i class="pi pi-phone"></i>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors['phone 1']">{{validation_errors['phone 1']}}</span>
                            </div>
                            <div
                                class="container-input-pdf  py-3  d-flex justify-content-center align-items-start flex-column">
                                <span class="p-input-icon-right w-100">
                                    <p-inputtext id="phone_2" type="text" placeholder="Phone 2" class="d-flex w-100"
                                        v-model="createform['phone 2']" required>
                                    </p-inputtext>
                                    <i class="pi pi-phone"></i>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors['phone 2']">{{validation_errors['phone 2']}}</span>
                            </div>
                            <div
                                class="container-input-pdf field-expanded py-3 d-flex justify-content-center align-items-start flex-column">
                                <span class="p-input-icon-right w-100">
                                    <p-inputtext id="address" type="text" placeholder="Address" class="d-flex w-100"
                                        v-model="createform.address" required>
                                    </p-inputtext>
                                    <i class="pi pi-map-marker"></i>
                                </span>
                                <span class="p-error-message"
                                    v-if="validation_errors?.address">{{validation_errors?.address}}</span>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
        <template #footer>
            <div class="d-flex justify-content-end">

                <p-button label="Create New Contact" icon="pi pi-plus" style="height: 45px; font-size:15px;"
                    class=" d-flex align-items-center p-button p-component  p-button-success p-ml-auto p-button-rounded   p-button-sm"
                    iconPos="right" @click="createContact">
                </p-button>
            </div>
        </template>
    </p-dialog>
    <p-datatable :value="clients" paginator="true" rows="10">
        <template #header>
            <div
                class="controls d-flex flex-md-row flex-column align-items-md-end align-items-center justify-content-md-between justify-content-center">
                <div class="d-flex flex-column justify-content-center align-items-start">
                    <span class="p-input-icon-left">
                        <i class="pi pi-search"></i>
                        <p-inputtext v-model="filters['global'].value" placeholder="Keyword Search"></p-inputtext>
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
            Loading Clients data. Please wait.
        </template>
        <p-column field="Nr" header="Nr">
            <template #body="{data, index}">
                {{index + 1}}
            </template>
        </p-column>
        <p-column field="name" header="Name"></p-column>
        <p-column field="email" header="Email"></p-column>
        <p-column field="phone_1" header="Phone 1"></p-column>
        <p-column field="phone_2" header="Phone 2"></p-column>
        <p-column field="address" header="Address"></p-column>
        <p-column field="updated_at" header="Update"></p-column>
        <p-column header="Actions" bodyStyleClass="p-text-center d-flex justify-content-center">
            <template #body="{data}">
                <div class="d-flex justify-content-center">
                    <p-button icon="pi pi-pencil" class="p-button-rounded p-button-sm p-button-text p-button-primary"
                        @click="openDialogEdit(data)">
                    </p-button>
                    <p-button icon="pi pi-trash" class="p-button-rounded p-button-sm p-button-text p-button-danger"
                        @click="deleteContact(data)">
                    </p-button>
                </div>
            </template>
    </p-datatable>
</div>
<style>

</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const {
        createApp,
        ref,
        reactive,
        onMounted
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
            "p-dialog": primevue.dialog
        },
        methods: {
            deleteContact(data) {
                Swal.fire({
                    "title": "Are you sure? this action cannot be undone.",
                    //decir que se perderan todo relacinado al cliente incluyendo aplicaciones activas
                    "text": "Everything related to the client will be lost including active applications",
                    "icon": "danger",
                    "showCancelButton": true,
                    "confirmButtonText": "Yes, delete it!",
                    "cancelButtonText": "No, cancel!",
                    "reverseButtons": true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`<?= RoutesService::get_api_base(); ?>deleteClientById/${data.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                        }).then(res => res.json()).then(res => {
                            if (res) {
                                Swal.fire({
                                    "title": "Deleted!",
                                    "text": "Client has been deleted.",
                                    "icon": "success",
                                    "confirmButtonText": "Ok"
                                }).then((result) => {
                                    this.clients = this.clients.filter((item) => {
                                        return item.id != data.id;
                                    });
                                });
                            }
                        });

                    }
                });
            },
            openDialogCreate() {
                this.dialog_create = true;
            },
            cleanCreate() {
                this.createform = {
                    name: null,
                    email: null,
                    'phone 1': null,
                    'phone 2': null,
                    address: null
                }
                this.validation_errors = {}
            },
            createContact() {

                let results = this.applyValidations(this.createform, this.createformvalidations);
                if (results.length > 0) {
                    for (let i = 0; i < results.length; i++) {
                        this.validation_errors[results[i].field] = results[i].message;
                    }
                    return;
                } else {
                    let data = {
                        name: this.createform.name,
                        email: this.createform.email,
                        phone_1: this.createform['phone 1'],
                        phone_2: this.createform['phone 2'],
                        address: this.createform.address
                    }
                    Clients.create(data).then(res => {
                        //hora actual en formato 2023-01-26 20:09:01
                        let update_at = new Date();
                        data.updated_at = update_at.getFullYear() + "-" + (update_at.getMonth() +
                                1) + "-" + update_at.getDate() + " " + update_at.getHours() + ":" +
                            update_at.getMinutes() + ":" + update_at.getSeconds();
                        this.clients.push(data);
                        this.cleanCreate();
                        this.dialog_create = false;

                    }).catch(err => {
                        console.log(err)
                    })
                }
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
                                if (field == null || field == "") {
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
            }

        },



        setup() {
            const clients = ref(<?= json_encode($data) ?>);
            const dialog_create = ref(false);
            const validation_errors = ref({});
            const filters = ref({
                global: {
                    value: null,
                    matchMode: FilterMatchMode.CONTAINS
                }
            });
            const createform = ref({
                name: null,
                email: null,
                "phone 1": null,
                "phone 2": null,
                address: null
            });
            const createformvalidations = ref({
                name: "max-length:50|min-length:3|alpha.space|required",
                email: "max-length:50|min-length:3|email|required",
                "phone 1": "max-length:50|min-length:5|numeric",
                "phone 2": "max-length:50|min-length:5|numeric",
                address: "max-length:50|min-length:3"
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
                filters,
                clients,
                dialog_create,
                createform,
                createformvalidations,
                validation_errors
            }
        }
    }
    createApp(app).use(primevue.config.default).mount('#app-ui-clients')
});
</script>
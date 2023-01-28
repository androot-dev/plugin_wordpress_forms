<?php class ViewController
{

    public function __construct($table, $methods)
    {
?>
<script>
class <?= $table; ?>Controller {
    constructor() {
        this.table = "<?= $table; ?>";
    }
    <?php if (in_array("update", $methods)) { ?>
    async update(id, data) {
        let params = "?";
        for (let key in data) {
            let next = "&";
            if (key == Object.keys(data)[Object.keys(data).length - 1]) {
                next = "";
            }
            params += key + "=" + data[key] + next;
        }

        let res = await fetch("<?= RoutesService::get_api_base(); ?>" + this.table + "/" + id + params, {
            method: 'PUT',
        });
        if (res) {
            return res;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
                footer: res
            })
            return false;
        }
    }
    <?php } ?>
    <?php if (in_array("create", $methods)) { ?>
    create(data, multiple = false) {
        let table = this.table;
        let sd = JSON.stringify(data);
        let validateJsson = (str) => {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }
        if (!validateJsson(sd)) {
            console.log("el siguiente no es un json valido:", data);
            return;
        }

        let post = new Promise((resolve, reject) => {

            fetch("<?= RoutesService::get_api_base(); ?>" + table + (multiple ? "?multiple=true" : ""), {
                    method: 'POST',
                    body: JSON.stringify(data),
                })
                .then(response => response.json()).then(data => {
                    if (data) {
                        if (data.error) {
                            reject(data);
                        } else {
                            resolve(data);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            footer: data
                        })
                        console.log(data);
                        reject(data);
                    }
                }).catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                        footer: error
                    })
                    console.log(data);
                    reject(error);
                });
        });
        return post;
    }
    <?php } ?>
    <?php if (in_array("delete", $methods)) { ?>
    delete(id) {
        let table = this.table;
        let post = new Promise((resolve, reject) => {

            fetch("<?= RoutesService::get_api_base(); ?>" + table + "/" + id, {
                    method: 'DELETE',
                })
                .then(response => response.json()).then(data => {
                    if (data) {
                        if (data.error) {
                            reject(data);
                        } else {
                            resolve(data);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            footer: data
                        })
                        console.log(data);
                        reject(data);
                    }
                }).catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                        footer: error
                    })
                    console.log(data);
                    reject(error);
                });
        });
        return post;
    }
    <?php } ?>
    <?php if (in_array("get", $methods)) { ?>
    get($id = null) {
        let table = this.table;
        let post = new Promise((resolve, reject) => {

            fetch("<?= RoutesService::get_api_base(); ?>" + table + ($id ? "/" + $id : ""), {
                    method: 'GET',
                })
                .then(response => response.json()).then(data => {
                    if (data) {
                        if (data.error) {
                            reject(data);
                        } else {
                            resolve(data);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            footer: data
                        })
                        console.log(data);
                        reject(data);
                    }
                }).catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                        footer: error
                    })
                    console.log(data);
                    reject(error);
                });
        });
        return post;
    }
    <?php } ?>
    <?php if (in_array("getWhere", $methods)) { ?>

    getWhere(where) {

        if (where != null && where != undefined && where != {}) {
            let params = "?";
            for (let key in where) {
                let next = "&";
                if (key == Object.keys(where)[Object.keys(where).length - 1]) {
                    next = "";
                }
                params += key + "=" + where[key] + next;
            }
            let table = this.table;
            let get = new Promise((resolve, reject) => {
                fetch("<?= RoutesService::get_api_base(); ?>" + table + "/where" + params, {
                        method: 'GET',
                    })
                    .then(response => response.json()).then(data => {
                        if (data) {
                            if (data.error) {
                                reject(data);
                            } else {
                                resolve(data);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                                footer: data
                            })
                            console.log("fail 1", data);
                            reject(data);
                        }
                    }).catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            footer: error
                        })
                        console.log("fail 2", data);
                        reject(error);
                    });
            });
            return get;
        } else {
            return new Promise((resolve, reject) => {
                reject("No se puede realizar la consulta sin parametros");
            });
        }

    }

    <?php } ?>
}

const <?= str_replace(" ", "", ucwords(str_replace("_", " ", $table))); ?> = new <?= $table; ?>Controller();
</script>
<?php
    }
}
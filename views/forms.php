<?php
//obtener instancia de wpdb
$db = new DatabaseController();
$data = $db->get("submits");
$data = $db->linkData($data, "id_form", "forms.name_forms");
$data = $db->linkData($data, "id_client", "clients.name");
?>

<div id="app" class="app py-5 px-4 app-forms-se">
    <div class="container">
        <h1>
            IUSI INVESTMENT CORP
        </h1>
        <div class="row">

        </div>
    </div>
</div>
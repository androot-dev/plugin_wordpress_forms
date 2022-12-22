<?php
//obtener instancia de wpdb
global $wpdb;
$api_url =  home_url() . "/wp-json/api/uiplugin/v1/forms";
$response = wp_remote_get($api_url);
$data = json_decode(wp_remote_retrieve_body($response), true);

?>
<div class="app py-5 px-4 app-forms-se">
    <div class="container">
        <h1>
            IUSI INVESTMENT CORP
        </h1>
        <div class="row">
            <table>
                <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>category</th>
                        <th>description</th>
                        <th>Download</th>
                        <th>created_at</th>
                        <th>updated_at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $row) {
                        echo "<tr>";
                        foreach ($row as $key => $value) {
                            echo "<td>$value</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $src = getConfig("root"); ?>
<script src="<?php echo $src; ?>js/vue3.js"></script>
<script>
const app = Vue.createApp({
    data() {
        return {
            message: 'Hello Vue 3!'
        }
    }
})
app.mount('.app')
</script>
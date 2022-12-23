<?php
//obtener instancia de wpdb

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
                        <th>Download</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>

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
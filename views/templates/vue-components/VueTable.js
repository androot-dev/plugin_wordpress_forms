export default {
    props: {
      table: Array,
    },
    template: `<table>
    <thead>
        <tr>
            <th v-for="(value, key) in data[0]">{{key}}</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="submit in data">
            <td>{{submit.Nr}}</td>
            <td>{{submit.Form}}</td>
            <td>{{submit.Client}}</td>
            <td>{{submit.Matters}}</td>
            <td>{{submit.Update}}</td>
        </tr>
    </tbody>
</table>`
}

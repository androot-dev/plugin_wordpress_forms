const embedPDF = {
    template: `
        <div class="embed-pdf">
            <embed :src="src" type="application/pdf" width="100%" height="100%">
        </div>
    `,
    props: {
        src: {
            type: String,
            required: true
        }
    },
    watch: {
        src: function (newVal, oldVal) {
            this.$emit('update:src', newVal);
            console.log('src changed');
        }


    }
};

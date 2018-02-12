<template>
</template>
<script>
    export default {
        name: 'DdvCrudList',
        props: {
            url: String,
        },
        methods: {
            onSubmit(link) {
                console.log(link);
                this.$emit('onSubmit',link);
            }
        }
    }
</script>
<template>
    <ddv-crud-main :breadcrumbData="breadcrumbData">

    </ddv-crud-main>
</template>
<script>
    export default {
        name: 'DdvCrudStore',
        props: {
            breadcrumbData: Object,
            tableDataUrl: String
        }
    }
</script>

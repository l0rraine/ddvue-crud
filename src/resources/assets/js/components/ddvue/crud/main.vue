<template>
    <el-container direction="vertical">
        <ddv-crud-breadcrumbs @onItemClick="handleBreadcrumbsClick" :config="breadcrumbData"></ddv-crud-breadcrumbs>
        <slot name="content"></slot>
    </el-container>
</template>
<script>
    export default {
        name: 'DdvCrudMain',
        props: {
            breadcrumbData: Object,
        },
        methods: {
            handleBreadcrumbsClick(url) {
                const that = this;
                that.$el.innerHTML = '';
                that.insertEl(url);
            }
        }
    }
</script>

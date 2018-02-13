<template>
    <el-container direction="vertical">
        <ddv-crud-breadcrumbs @onItemClick="handleBreadcrumbsClick" :data="breadcrumbData"></ddv-crud-breadcrumbs>
        <hr />

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
                this.$http.get(url).then(function (response) {
                    const v = response.data;
                    let MyComponent = Vue.extend({
                        template: v
                    });
                    let component = new MyComponent().$mount();
                    that.$el.innerHTML = '';
                    that.$el.appendChild(component.$el)
                });
            }
        }
    }
</script>

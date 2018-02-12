<template>
    <el-container direction="vertical">
        <ddv-breadcrumbs @onClick="handleBreadcrumbsClick"></ddv-breadcrumbs>
        <slot name="main"></slot>
    </el-container>
</template>
<script>
    export default {
        name: 'DdvCrudMain',
        props: {
            url: String,
        },
        methods: {
            handleBreadcrumbsClick(url) {
                console.log(url);
                //this.sendToMain(url);
            },
            sendToMain(url) {
                this.$http.get(url).then(function (response) {
                    const v = response.data;
//                    new Vue({
//                        el: '#main',
//                        render: function (h) {
//                            return h('div', {
//                                attrs: {
//                                    id: '#main'
//                                },
//                                domProps: {
//                                    innerHTML: v
//                                },
//                            })
//                        }
//                    })
                    let MyComponent = Vue.extend({
                        template: v
                    });
                    let component = new MyComponent().$mount();
                    document.getElementById('main').innerHTML='';
                    document.getElementById('main').appendChild(component.$el)
                });
            }
        }
    }
</script>
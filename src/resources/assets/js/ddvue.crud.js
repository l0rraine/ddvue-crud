
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');



window.Vue = require('vue');

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
Vue.use(ElementUI);

Vue.prototype.$http = axios;

Vue.mixin({
    methods:{
        sendTo(id,url,callback=function(){}) {
            this.$http.get(url).then(function (response) {
                const v = response.data;
                let MyComponent = Vue.extend({
                    template: v
                });
                let component = new MyComponent().$mount();
                document.getElementById(id).innerHTML = '';
                document.getElementById(id).appendChild(component.$el)
                callback();
            });
        },
        insertEl(that, url){
            if (url === '') return;
            that.$http.get(url).then(function (response) {
                const v = response.data;
                let MyComponent = Vue.extend({
                    template: v
                });
                let component = new MyComponent().$mount();
                that.$el.appendChild(component.$el)
            });
        },
        getUrlPrefix(){
            return window.config.dashboard_url_prefix;
        },
        getMainUrl(){
            return window.config.main_url;
        }
    }

});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import DdvCrudDatatable from './components/ddvue/crud/partials/datatable.vue';
Vue.component(DdvCrudDatatable.name, DdvCrudDatatable);


import DdvCrudList from './components/ddvue/crud/list.vue';
Vue.component(DdvCrudList.name, DdvCrudList);


import DdvDatatableRecursiveTitle from './components/ddvue/crud/partials/datatable-recursive-title.vue';
Vue.component(DdvDatatableRecursiveTitle.name, DdvDatatableRecursiveTitle);

import DdvCrudSelectRecursiveOption from './components/ddvue/crud/partials/select-recursive-option.vue'
Vue.component(DdvCrudSelectRecursiveOption.name, DdvCrudSelectRecursiveOption);

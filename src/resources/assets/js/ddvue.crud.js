
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


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import DdvCrudBreadcrumbs from './components/ddvue/crud/breadcrumbs.vue';
Vue.component(DdvCrudBreadcrumbs.name, DdvCrudBreadcrumbs);


import DdvCrudForm from './components/ddvue/crud/form.vue';
Vue.component(DdvCrudForm.name, DdvCrudForm);

import DdvCrudMain from './components/ddvue/crud/main.vue';
Vue.component(DdvCrudMain.name, DdvCrudMain);

import DdvCrudList from './components/ddvue/crud/list.vue';
Vue.component(DdvCrudList.name, DdvCrudList);






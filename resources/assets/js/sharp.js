import './polyfill';
import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import { install as VueGoogleMaps } from './vendor/vue2-google-maps';
import Notifications from 'vue-notification';

import SharpCommands from 'sharp-commands';
import SharpDashboard from 'sharp-dashboard';
import SharpEntityList from 'sharp-entity-list';
import SharpFilters from 'sharp-filters';
import SharpForm from 'sharp-form';
import SharpShow from 'sharp-show';
import SharpUI from 'sharp-ui';
import SharpSearch from 'sharp-search';

import ActionView from './components/ActionView.vue';
import LeftNav from './components/LeftNav.vue';
import {
    NavSection,
    NavItem,
} from 'sharp-ui';

import { store as getStore } from './store/store';
import { router as getRouter } from "./router";

Vue.use(Notifications);
Vue.use(VueGoogleMaps, {
    installComponents: false
});

Vue.use(VueRouter);
Vue.use(Vuex);

const router = getRouter();
const store = getStore();

Vue.use(SharpCommands, { store, router });
Vue.use(SharpDashboard, { store, router });
Vue.use(SharpEntityList, { store, router });
Vue.use(SharpFilters, { store, router });
Vue.use(SharpForm, { store, router });
Vue.use(SharpShow, { store, router });
Vue.use(SharpUI, { store, router });
Vue.use(SharpSearch, { store, router });


Vue.component('sharp-action-view', ActionView);
Vue.component('sharp-left-nav', LeftNav);
Vue.component('sharp-nav-section', NavSection);
Vue.component('sharp-nav-item', NavItem);

new Vue({
    el: "#sharp-app",

    store,
    router,
});





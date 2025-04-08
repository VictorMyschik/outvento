require('./bootstrap');

import {createApp} from 'vue';
import mrp from './components/MrPopupForm.vue';
//import main_page from './components/main_page.vue';
import nav_bar from './components/nav_bar.vue';
import account_travel_list from './components/account/travel/page.vue';
import new_travel from './components/account/travel/new_travel.vue';
import v_select from "vue-select";
import alert_modal from './components/alert.vue';
import slider from './components/main/slider.vue';
import activities from './components/main/activities.vue';
//import public_search_page from "./components/public_search_page.vue";

import 'vue-select/dist/vue-select.css';

const app = createApp({
    components: {
        mrp,
       // main_page,
        nav_bar,
        account_travel_list,
        new_travel,
        v_select,
        alert_modal,
        slider,
        activities,
        //public_search_page
    }
});

app.mount('#app');



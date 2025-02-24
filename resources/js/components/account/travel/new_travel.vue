<template>
    <li class="nav-item">
        <a class="nav-link" href="#">
            <span class="mr-nav-link-color mr-color-green">
                        <mrp btn_name="новое"
                             @response="redirectToTravel"
                             :route_url="router('account.travel.base.form', {'travel_id': 0})"
                             class_arr=""></mrp>
            </span>
        </a>
    </li>
</template>

<script>
import mrp from './../../MrPopupForm.vue';

export default {
    components: {
        mrp
    },
    name: "new_travel",
    data() {
        return {
            urlList: {
                'account.travel.base.form': '/account/travel/{travel_id}/base/form',
                "account.travel.page": "/account/travel/{travel_id}/page",
            },
            list: null,
        }
    },

    methods: {
        router: function (route, params) {
            let url = this.urlList[route];
            for (let key in params) {
                url = url.replace('{' + key + '}', params[key]);
            }
            return url;
        },

        redirectToTravel: function (response) {
            console.log(response);

            if (response.result !== true) {
                console.log('Error');
                return;
            }
            window.location.href = this.router('account.travel.page', {'travel_id': response.content});
        }
    }
}
</script>

<style scoped>

</style>

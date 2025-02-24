<template>
    <div class="row col-md-12 col-sm-12 col-lg-12">
        <div class="col-md-2 col-sm-12 p-3 mr-left-side" style="border-radius: 5px; word-break: break-all;">
            <h4>Описание</h4>
            <h4>Участники</h4>
            <h4>Снаряжение</h4>
            <h4>Питание</h4>
            <h4>Маршрут</h4>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="row col-md-12">
                    <div class="d-inline col-md-9 text-nowrap">
                        <mrp title="Изменить"
                             @response="getTravelDetails"
                             :route_url="router('account.travel.base.form', {'travel_id': travel_id})"
                             class_arr="mr-btn-primary fa fa-pen">
                        </mrp>
                        <span class="font-weight-bolder ml-1">{{ travelDetails.title }}</span>
                        <div><span class="mr-color-green-dark">{{visibleKind.name}}</span></div>
                    </div>
                    <div class="d-inline col-md-3 text-nowrap text-right">
                        <div>Обновлено: {{ travelDetails.updated_at }}</div>
                        <div class="">{{ travelStatus.name }}</div>
                    </div>
                </div>

                <div class="row col-md-12 mt-2">
                    <div class="d-inline col-md-9 text-nowrap">
                        <h5>{{ country.name }}</h5>
                        <h5>{{ travelType.name }}</h5>
                    </div>
                </div>
            </div>
            <div class="row col-md-10">{{ travelDetails.description }}</div>
        </div>
    </div>
</template>

<script>
import mrp from './../../MrPopupForm.vue';

export default {
    components: {
        mrp
    },
    props: ['travel_id'],
    name: "page",

    data() {
        return {
            urlList: {
                'api.travel.details': '/api/travel/details',
                'account.travel.base.form': '/account/travel/{travel_id}/base/form',
            },
            travelDetails: {},
            country: {},
            travelType: {},
            travelStatus: {},
            visibleKind: {},
        }
    },
    created() {
        this.getTravelDetails();
    },

    methods: {
        router: function (route, params) {
            let url = this.urlList[route];
            for (let key in params) {
                url = url.replace('{' + key + '}', params[key]);
            }
            return url;
        },

        getTravelDetails: function () {
            axios.post(this.urlList['api.travel.details'], {'travel_id': this.travel_id}, {}).then(response => {
                    if (response.data.result !== true) {
                        console.log('Error');
                        return;
                    }

                    this.travelDetails = response.data.content;
                    this.country = this.travelDetails.country;
                    this.travelType = this.travelDetails.travel_type;
                    this.travelStatus = this.travelDetails.status;
                    this.visibleKind = this.travelDetails.visible_kind;
                }
            );
        },
    }
}
</script>

<style scoped>

</style>

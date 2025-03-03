<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col mt-5">
                <v_select :options="counties"
                          v-model="country"
                          :placeholder="countryPlaceholder"
                          aria-autocomplete="inline"
                >
                </v_select>
            </div>
            <div class="col mt-5">
                <v_select :options="travelTypes"
                          v-model="travelType"
                          :placeholder="travelTypePlaceholder"
                          aria-autocomplete="inline"
                >
                </v_select>
            </div>
        </div>
    </div>
</template>

<script>
import v_select from 'vue-select';

export default {
    components: {
        v_select
    },
    name: 'main_page',
    data() {
        return {
            urlList: {
                "api.reference.full": "/api/reference/full",
            },
            country: null,
            counties: [],
            countryPlaceholder: null,

            travelType: null,
            travelTypes: [],
            travelTypePlaceholder: null,
        }
    },
    created: function () {
        this.getForm();
    },
    methods: {
        getForm: function () {
            axios.post(this.urlList['api.reference.full']).then(response => {
                    this.buildCountries(response.data.content.countries);
                    this.buildTravelTypes(response.data.content.travelTypes);
                }
            );
        },
        buildCountries: function (data) {
            this.countryPlaceholder = data['title'];

            for (let key in data.options) {
                console.log(data.options[key]);
                this.counties.push({
                    label: data.options[key],
                    id: key,
                });
            }
        },
        buildTravelTypes: function (data) {
            this.travelTypePlaceholder = data['title'];

            for (let key in data.options) {
                this.travelTypes.push({
                    label: data.options[key],
                    id: key,
                });
            }
        },
    },
}
</script>
<style>

</style>

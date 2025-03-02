<template>
    <div class="container border">
        <div class="row col-md-12 justify-content-center">
            <div class="mt-5">
                <v_select :options="counties"
                         v-model="country"
                         placeholder="Select Country"
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
                "api.reference.country.list": "/api/reference/country/list",
            },
            country: null,
            setSelected: 0,
            counties: [],
        }
    },
    created: function () {
        this.getForm();
    },
    methods: {
        getForm: function () {
            axios.post(this.urlList['api.reference.country.list']).then(response => {
                    this.buildCountriesOptions(response.data.content);
                }
            );
        },
        buildCountriesOptions: function (data) {
            for (let key in data) {
                this.counties.push({
                    label: data[key],
                    id: key,
                });
            }
        }
    },
}
</script>
<style>

</style>

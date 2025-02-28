<template>
    <div class="container border">
        <div class="row col-md-12 justify-content-center">
            <div class="mt-5">
                <vSelect :options="counties"
                         v-model="country"
                         placeholder="Select Country"
                         aria-autocomplete="inline"
                >
                </vSelect>
            </div>
        </div>
    </div>
</template>
<script>
import vSelect from 'vue-select'
export default {
    components: {
        vSelect,
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
@import "vue-select/dist/vue-select.css";

</style>

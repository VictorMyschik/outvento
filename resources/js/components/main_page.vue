<template>
    <div class="container">
        <div class="row justify-content-center mr-background-form">
            <v_select :options="counties"
                      v-model="country"
                      :placeholder="countryPlaceholder"
                      aria-autocomplete="inline"
                      class="col my-1"
            >
            </v_select>
            <v_select :options="travelTypes"
                      v-model="travelType"
                      :placeholder="travelTypePlaceholder"
                      aria-autocomplete="inline"
                      class="col my-1"
            >
            </v_select>
            <input type="date" v-model="date_from" class="col mx-2 my-1" style="">
            <input type="date" v-model="date_to" class="col mx-2 my-1">
            <button @click="search" class="col mr-btn-primary mx-2 my-1"><i v-if="runSearch"
                                                                            class="fa fa-spinner fa-spin"></i>search
            </button>
        </div>

        <div v-if="runSearch" class="row justify-content-center mr-background-form">
            <span><i class="fa fa-spinner fa-spin"></i> searching</span>
        </div>

        <div v-if="searchResultList" class="row justify-content-center mr-background-form">
            <div class="result-item-container">
                <div v-for="travel in searchResultList" class="result-item-block">
                    <div><b>{{ travel['title'] }}</b></div>
                    <div>
                        <span>{{ travel['dateFrom'] }}</span> - <span>{{ travel['dateTo'] }}</span>
                    </div>
                    <div>
                        <i class="fa fa-bicycle"></i>
                        <i class="fa fa-hiking"></i>
                        <i class="fa fa-mountain"></i>
                        <i class="fa fa-people-group"></i>
                    </div>
                    <div class="text-muted">{{ travel['preview'] }}</div>
                </div>
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
                "api.travels.search": "/api/travels/search",
            },
            country: null,
            counties: [],
            countryPlaceholder: null,

            travelType: null,
            travelTypes: [],
            travelTypePlaceholder: null,

            date_from: null,
            date_to: null,

            searchResultList: null,
            runSearch: false,
        }
    },
    created: function () {
        this.getForm();
    },
    methods: {
        search: function () {
            let data = {
                country: this.country ? this.country.id : null,
                travelType: this.travelType ? this.travelType.id : null,
                dateFrom: this.date_from,
                dateTo: this.date_to,
            };
            this.runSearch = true;
            axios.post(this.urlList['api.travels.search'], data).then(response => {
                    this.buildTravelResultList(response.data.content);
                }
            );
            this.runSearch = false;
        },
        buildTravelResultList: function (data) {
            if (!data.length) {
                this.searchResultList = [{
                    title: 'Not found',
                    preview: '',
                }];

                return;
            }

            this.searchResultList = data;
        },
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
<style scoped>
.result-item-container {
    display: block;
}

.result-item-block {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    margin: 8px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.result-item-block:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.result-item-title {
    font-size: 1.25em;
    font-weight: bold;
    margin-bottom: 8px;
}

.result-item-preview {
    font-size: 1em;
    color: #666;
}
</style>

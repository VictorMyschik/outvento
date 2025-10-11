<template>
    <div class="">
        <section class="position-relative" style="height: 70vh;">
            <div class="slider-home1 overflow-hidden swiper-slide">
                <div class="silider-image">
                    <img src="/images/slide1.jpg" alt="Travel and adventure" class="image-slide">
                </div>

                <div class="container slider-content">
                    <div class="col-lg-8">
                        <h1 class="title-slide text-white">Travel &amp; <br>adventure</h1>
                        <p class="text-white mt-5" style="font-size: 1.2rem;"> {{ lang['slogan'] }} </p>
                    </div>
                    <button class="mr-btn-primary mx-1">Board</button>
                    <button class="mr-btn-primary mx-1">Bike</button>
                    <button class="mr-btn-primary mx-1">Travel</button>
                </div>

                <div class="container slider-content-search mr-background-form">
                    <div class="row">
                        <v_select :options="counties.sort()"
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
                        <button @click="search" class="col mr-btn-primary mx-2 my-1">
                            <i v-if="runSearch" class="fa fa-spinner fa-spin"></i> {{ lang['search'] }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import alert_modal from './alert.vue';
import v_select from 'vue-select';

import '../components/template/jquery.min.js';
import '../components/template/jquery.nice-select.min.js';
import '../components/template/bootstrap.min.js';
import '../components/template/swiper-bundle.min.js';
import '../components/template/swiper.js';
import '../components/template/plugin.js';
import '../components/template/count-down.js';
import '../components/template/countto.js';
import '../components/template/jquery.fancybox.js';
import '../components/template/jquery.magnific-popup.min.js';
import '../components/template/price-ranger.js';
import '../components/template/textanimation.js';
import '../components/template/wow.min.js';
import '../components/template/shortcodes.js';
import '../components/template/main.js';

export default {
    components: {
        v_select
    },
    name: 'main_page',
    props: [
        'lang',
    ],
    data() {
        return {
            isAlertVisible: false,
            alertMessage: 'This is an alert message!',

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
            maxMemberFrom: null,
            maxMemberTo: null,
            freeMember: null,

            searchResultList: null,
            runSearch: false,
        }
    },
    created: function () {
        this.getForm();
    },
    methods: {
        showAlert() {
            this.isAlertVisible = true;
        },
        scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        },
        search: function () {
            let data = {
                country: this.country ? this.country.id : null,
                travelType: this.travelType ? this.travelType.id : null,
                dateFrom: this.date_from,
                dateTo: this.date_to,
                maxMemberFrom: this.maxMemberFrom,
                maxMemberTo: this.maxMemberTo,
                freeMember: this.freeMember,
            };

            if (this.runSearch === false) {
                this.runSearch = true;
                axios.post(this.urlList['api.travels.search'], data)
                    .then(response => {
                        this.buildTravelResultList(response.data.content);
                        this.runSearch = false;
                        this.scrollToSection('results');
                    })
                    .catch(error => {
                        console.error(error);
                        this.runSearch = false; // Отключаем спиннер в случае ошибки
                    });
            }

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

            this.counties.sort((a, b) => a.label.localeCompare(b.label));
        },
        buildTravelTypes: function (data) {
            this.travelTypePlaceholder = data['title'];

            for (let key in data.options) {
                this.travelTypes.push({
                    label: data.options[key],
                    id: key,
                });
            }

            this.travelTypes.sort((a, b) => a.label.localeCompare(b.label));
        },
    },
}
</script>

<style scoped>
.result-item-container {
    display: block;
    max-height: 85vh;
    overflow-y: auto;
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

.mr-filter {
    display: inline-block;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px;
    margin: 8px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}
</style>

<template>
    <div>

        <section>
            <div v-if="searchResultList" class="justify-content-center mr-background-form">
                <div href="#filter_toggle" class="mr-cursor" data-bs-toggle="collapse">
                    <h5>{{ lang['filter'] }}</h5>
                    <div v-if="!runSearch">{{ lang['found'] }}: {{ searchResultList.length }}</div>
                </div>
                <div id="filter_toggle" ref="filter_toggle" class="row collapse">
                    <div class="col">
                        <label>{{ lang['max_member_from'] }}</label>
                        <input type="number" class="form-control" v-model="maxMemberFrom">
                    </div>
                    <div class="col">
                        <label>{{ lang['max_member_to'] }}</label>
                        <input type="number" class="form-control" v-model="maxMemberTo">
                    </div>
                    <div class="col">
                        <label>{{ lang['free_members'] }}</label>
                        <input type="number" class="form-control" v-model="freeMember">
                    </div>
                </div>
            </div>
        </section>
        <section id="results">
            <div v-if="searchResultList" class="row justify-content-center mr-background-form">
                <div class="row justify-content-center">
                    <div v-if="maxMemberFrom" class="col mr-filter col-auto mx-2">{{ lang['max_member_from'] }}<i
                        @click="maxMemberFrom=null; search()" class="mr-btn-off fa fa-close mx-2 mr-cursor"></i></div>
                    <div v-if="maxMemberTo" class="col mr-filter col-auto mx-2">{{ lang['max_member_to'] }}<i
                        @click="maxMemberTo=null; search()" class="mr-btn-off fa fa-close mx-2 mr-cursor"></i></div>
                    <div v-if="freeMember" class="col mr-filter col-auto mx-2">{{ lang['free_members'] }}<i
                        @click="freeMember=null; search()" class="mr-btn-off fa fa-close mx-2 mr-cursor"></i></div>
                </div>
                <div class="result-item-container">
                    <div v-for="travel in searchResultList" class="result-item-block">
                        <div>
                            <h5>
                                <img class="icon"
                                     :title="travel['travelType']['name']"
                                     :src="travel['travelType']['icon']"
                                     :alt="travel['travelType']['name']">
                                <span v-if="travel['members']['maxMember']" :title="travel['members']['title']">
                                {{ travel['members']['maxM    ember'] }}({{ travel['members']['existsMembers'] }})
                            </span>
                                {{ travel['title'] }}
                            </h5>
                        </div>
                        <div>
                            <span>{{ travel['dateFrom'] }}</span> - <span>{{ travel['dateTo'] }}</span>
                        </div>
                        <div class="text-muted">{{ travel['preview'] }}</div>
                        <div class="text-muted">{{ lang['owner'] }}: {{ travel['owner'] }}</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import v_select from 'vue-select';

export default {
    components: {
        v_select
    },
    name: 'public_search_page',
    props: [
        'lang',
    ],
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
        scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({behavior: 'smooth'});
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
</style>

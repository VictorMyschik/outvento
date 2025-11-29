<template>
    <section class="slider relative">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="slider-home1 relative overflow-hidden swiper-slide">
                    <div class="silider-image">
                        <img src="assets/images/slide/slide1.jpg" alt="Image" class="image-slide">
                    </div>
                    <div class="slider-content">
                        <div class="tf-container">
                            <div class="row">
                                <div class="col-lg-8">
                                    <span class="sub-title text-main font-yes fs-28-46 fadeInDown">
                                        Explore the world
                                    </span>
                                    <h1 class="title-slide text-white mb-32 fadeInDown">Travel &amp; <br> adventure</h1>
                                    <p class="des text-white mb-45 fadeInDown">{{ lang['slogan'] }} </p>
                                </div>

                                <!-- ============ МОБИЛЬНАЯ ВЕРСИЯ (до md) ============ -->
                                <div class="container mr-background-form py-3 py-md-0">
                                    <div class="row d-md-none g-3">
                                        <div class="col-12">
                                            <label class="form-label-title d-block">{{ countryPlaceholder }}</label>
                                            <select class="form-select" v-model="country">
                                                <option disabled value="">{{ countryPlaceholder }}</option>
                                                <option v-for="option in counties" :key="option.id" :value="option.id">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label-title d-block">{{ travelTypePlaceholder }}</label>
                                            <select class="form-select" v-model="travelType">
                                                <option disabled value="">{{ travelTypePlaceholder }}</option>
                                                <option v-for="option in travelTypes" :key="option.id"
                                                        :value="option.id">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label-title d-block">{{ lang['date_from'] }}</label>
                                            <input type="date" v-model="date_from" class="form-control">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label-title d-block">{{ lang['date_to'] }}</label>
                                            <input type="date" v-model="date_to" class="form-control">
                                        </div>

                                        <div class="col-12">
                                            <button @click="search" class="btn btn-primary w-100">
                                                {{ lang['search'] }}
                                            </button>
                                        </div>
                                    </div>
                                </div>


                                <div class="d-none d-md-block container mr-background-form">
                                    <div class="row forms">
                                        <label class="col form-label-mobile">
                                            <span class="form-label-title">{{ countryPlaceholder }}</span>
                                            <select class="form-control select mt-2" v-model="country">
                                                <option disabled value="">{{ countryPlaceholder }}</option>
                                                <option v-for="option in counties" :key="option.id" :value="option.id">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </label>

                                        <label class="col form-label-mobile">
                                            <span class="form-label-title">{{ travelTypePlaceholder }}</span>
                                            <select class="form-control select mt-2" v-model="travelType">
                                                <option disabled value="">{{ travelTypePlaceholder }}</option>
                                                <option v-for="option in travelTypes" :key="option.id"
                                                        :value="option.id">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </label>

                                        <label class="col form-label-mobile">
                                            <span class="form-label-title">{{ lang['date_from'] }}</span>
                                            <input type="date" v-model="date_from" class="form-control mt-2">
                                        </label>

                                        <label class="col form-label-mobile">
                                            <span class="form-label-title">{{ lang['date_to'] }}</span>
                                            <input type="date" v-model="date_to" class="form-control mt-2">
                                        </label>

                                        <button @click="search" class="col form-control btn btn-primary mt-2 btn-mobile"
                                                style="margin-right: 10px;">
                                            {{ lang['search'] }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>

import v_select from 'vue-select';

export default {
    components: {
        v_select
    },
    name: 'slider',
    props: [
        'lang',
    ],
    data() {
        return {
            isAlertVisible: false,
            alertMessage: 'This is an alert message!',

            urlList: {
                "api.reference.full": "/api/reference/full",
                "travels.search.page": "/travels/search",
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
                section.scrollIntoView({behavior: 'smooth'});
            }
        },
        search: function () {
            if (!this.country) {
                alert('Please select country');

                return;
            }

            if (!this.travelType) {
                alert('Please select travel type');

                return;
            }
            let data = {
                country: this.country,
                travelType: this.travelType,
                dateFrom: this.date_from,
                dateTo: this.date_to,
            };

            window.location.href = this.urlList["travels.search.page"] + "?" + new URLSearchParams(data).toString();
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
.mr-background-form {
    background-color: rgba(253, 253, 253, 0.5);
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    padding: 10px 30px;
}

.forms {
    display: flex;
    align-items: center;
}

.form-control {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
    height: 40px;
    box-sizing: border-box;
}

.form-label-title {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #222;
    margin-bottom: 4px;
    letter-spacing: 0.01em;
}

.slider-home1 {
    .silider-image {
        &::before {
            background: linear-gradient(90deg, rgba(4, 27, 40, 1), rgba(0, 0, 0, 0));
            position: absolute;
            width: 100%;
            height: 100%;
            content: '';
            z-index: 2;
        }

        img {
            position: absolute;
        }

        .image-slide {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    .slider-content {
        position: relative;
        z-index: 3;
        padding-top: 144px;
        padding-bottom: 202px;

        .title-slide {
            text-transform: uppercase;
            font-size: 70px;
            font-weight: 700;
            line-height: 95px;
        }
    }
}

@media (max-width: 768px) {
    .mr-background-form {
        padding: 10px 5px;
        border-radius: 6px;
    }

    .forms {
        flex-direction: column;
        align-items: stretch;
    }

    .form-label-mobile {
        width: 100%;
        margin-bottom: 10px;
        font-size: 15px;
    }

    .form-label-title {
        font-size: 13px;
        margin-bottom: 2px;
    }

    .form-control, .col, .btn-mobile {
        width: 100% !important;
        margin-right: 0 !important;
        font-size: 16px;
        min-height: 44px;
    }

    .slider-content {
        padding-top: 40px;
        padding-bottom: 40px;
    }

    .title-slide {
        font-size: 32px !important;
        line-height: 40px !important;
    }
}
</style>

<template>
    <div>
        <section class="breadcumb-section">
            <div class="tf-container">
                <div class="row">
                    <div class="col-lg-12 center z-index1">
                        <h3 class="title">{{ lang['search_slogan'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="slider-home1 relative overflow-hidden swiper-slide">
                <div class="slider-content">
                    <div class="tf-container">
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
                                    <input type="date" v-model="dateFrom" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label-title d-block">{{ lang['date_to'] }}</label>
                                    <input type="date" v-model="dateTo" class="form-control">
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
                                        <option v-for="option in counties" :key="option.id" :value="option.id">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </label>

                                <label class="col form-label-mobile">
                                    <span class="form-label-title">{{ travelTypePlaceholder }}</span>
                                    <select class="form-control select mt-2" v-model="travelType">
                                        <option v-for="option in travelTypes" :key="option.id" :value="option.id">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </label>

                                <label class="col form-label-mobile">
                                    <span class="form-label-title">{{ lang['date_from'] }}</span>
                                    <input type="date" v-model="dateFrom" class="form-control mt-2">
                                </label>

                                <label class="col form-label-mobile">
                                    <span class="form-label-title">{{ lang['date_to'] }}</span>
                                    <input type="date" v-model="dateTo" class="form-control mt-2">
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
        </section>

        <!-- Widget archieve tour -->
        <section class="archieve-tour">
            <div class="tf-container">

                <div class="row">
                    <div class="col-lg-4">
                        <form action="/" class="sider-bar-tour-package">
                            Accepts Credit Cards
                            <div class="widget-filter mb-40">
                                <h6 class="title-tour">Search by Filter</h6>
                                <div class="group-check-box-wrap">
                                    <div class="checkbox">
                                        <input id="check3" type="checkbox" name="check" value="check">
                                        <label for="check3">Adults</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-8 listing-list-car-wrap">
                        <form action="/" class="tf-my-listing mb-37">
                            <div class="row align-center">
                                <div class="col-sm-5">
                                    <p class="showing">Showing <span class="text-main">12</span> of 21 Results </p>
                                </div>
                                <div class="col-sm-7 group-bar-wrap flex-six">
                                    <div class="listing-all-wrap">
                                        <div class="flex-three">
                                            <div class="toolbar-list">
                                                <div class="form-group">
                                                    <a class="btn-display-listing-grid active">
                                                        <i class="icon-list"></i>
                                                    </a>
                                                </div>
                                                <div class="form-group">
                                                    <a class="btn-display-listing-list">
                                                        <i class="icon-Group-1000001297"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="listing-list-car-grid mb-60">
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">
                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">

                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">

                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">

                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">

                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-listing box-sd">
                                <a href="tour-single.html" class="tour-listing-image">
                                    <div class="badge-top flex-two">
                                        <span class="feature">Featured</span>
                                        <div class="badge-media flex-five">
                                            <span class="media"><i class="icon-Group-1000002909"></i>5</span>
                                            <span class="media"><i class="icon-Group-1000002910"></i>2</span>
                                        </div>
                                    </div>
                                    <img src="http://travel.test/images/gallery/gl1.jpg" alt="Image Listing">

                                </a>
                                <div class="tour-listing-content">
                                    <span class="map"><i class="icon-Vector4"></i>United States USA</span>
                                    <h3 class="title-tour-list"><a href="tour-single.html">Days and 6 nights
                                        From Moscow</a>
                                    </h3>
                                    <div class="review">
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <i class="icon-Star"></i>
                                        <span>(1 Review)</span>
                                    </div>
                                    <div class="icon-box flex-three">
                                        <div class="icons flex-three">
                                            <i class="icon-time-left"></i>
                                            <span>5 days</span>
                                        </div>
                                        <div class="icons flex-three">
                                            <svg width="21" height="16" viewBox="0 0 21 16" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4.34766 4.79761C4.34766 2.94013 5.85346 1.43433 7.71094 1.43433C9.56841 1.43433 11.0742 2.94013 11.0742 4.79761C11.0742 6.65508 9.56841 8.16089 7.71094 8.16089C5.85346 8.16089 4.34766 6.65508 4.34766 4.79761Z"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path
                                                    d="M9.5977 15.1797H2.46098C1.34827 15.1797 0.558268 14.0954 0.898984 13.0362C1.80408 10.222 4.57804 8.18566 7.69301 8.18566C9.17897 8.18566 10.5566 8.64906 11.6895 9.43922"
                                                    stroke="currentColor" stroke-width="1.7"
                                                    stroke-miterlimit="10" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M17.1035 15.1797V9.02734" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M20.1797 12.1035H14.0273" stroke="currentColor"
                                                      stroke-width="1.7" stroke-miterlimit="10"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>12 Person</span>
                                        </div>
                                    </div>
                                    <div class="flex-two">
                                        <div class="price-box flex-three">
                                            <p>From <span class="price-sale">$169.00</span></p>
                                            <span class="price">$199.00</span>
                                        </div>
                                        <div class="icon-bookmark">
                                            <i class="icon-Vector-151"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 ">
                                <ul class="tf-pagination flex-three">
                                    <li>
                                        <a class="pages-link" href="#"> <i class="icon-29"></i></a>
                                    </li>
                                    <li>
                                        <a class="pages-link" href="#">1</a>
                                    </li>
                                    <li class="pages-item active" aria-current="page">
                                        <a class="pages-link" href="#">2</a>
                                    </li>
                                    <li><a class="pages-link" href="#">3</a></li>
                                    <li>
                                        <a class="pages-link" href="#"><i class=" icon--1"></i></a>
                                    </li>
                                </ul>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>
        <!-- Widget archieve tour -->
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

            // from query parameters
            dateFrom: null,
            dateTo: null,
            maxMemberFrom: null,
            maxMemberTo: null,
            freeMember: null,

            searchResultList: null,
            runSearch: false,
        }
    },
    created() {
        this.getForm();
    },
    methods: {
        scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({behavior: 'smooth'});
            }
        },

        getForm: function () {
            const params = new URLSearchParams(window.location.search);

            axios.post(this.urlList['api.reference.full']).then(response => {
                    this.buildCountries(response.data.content.countries, params.get('country'));
                    this.buildTravelTypes(response.data.content.travelTypes, params.get('travelType'));
                }
            );

            this.dateFrom = params.get('dateFrom');
            this.dateTo = params.get('dateTo');
            //this.maxMemberFrom = q.maxMemberFrom || null;
            //this.maxMemberTo = q.maxMemberTo || null;
            //this.freeMember = q.freeMember || null;

        },

        buildCountries: function (data, selectedId) {
            this.countryPlaceholder = data['title'];

            for (let key in data.options) {
                this.counties.push({
                    label: data.options[key],
                    id: key,
                });

                if (selectedId === key) {
                    this.country = key;
                }
            }

            this.counties.sort((a, b) => a.label.localeCompare(b.label));
        },

        buildTravelTypes: function (data, selectedId) {
            this.travelTypePlaceholder = data['title'];

            for (let key in data.options) {
                this.travelTypes.push({
                    label: data.options[key],
                    id: key,
                });

                if (selectedId === key) {
                    this.travelType = key;
                }
            }

            this.travelTypes.sort((a, b) => a.label.localeCompare(b.label));
        },
    },
}
</script>

<style scoped>
.breadcumb-section {
    padding: 80px 0 0px 0;
}

.mr-background-form {
    background-color: rgba(253, 253, 253, 0.5);
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    padding: 10px 30px 0 30px;
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
    .slider-content {
        position: relative;
        z-index: 3;
    }
}

.slider-content {
    position: relative;
    z-index: 3;
    padding-top: 30px;
    padding-bottom: 30px;
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
}

</style>

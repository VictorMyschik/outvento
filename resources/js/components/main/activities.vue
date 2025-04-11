<template>
    <section class="relative tf-widget-activities pd-main overflow-hidden">
        <div class="tf-container">
            <div class="row z-index3 relative">
                <div class="col-lg-12 mb-60">
                    <div class="clip-text">Activities</div>
                </div>
                <div class="flex col-lg-12 my-2">
                    <div v-for="travelType in travel_type_list" @click="activeActivity = travelType.id"
                         class="mx-2 activity-btn my-2">
                        {{ travelType.name }}
                    </div>
                </div>
                <div class="col-lg-12">
                    <swiper :slides-per-view="3" :space-between="20" navigation pagination>
                        <swiper-slide v-for="example in travel_examples[activeActivity]" :key="example.id">
                            <div class="card">
                                <img :src="example.images[0]?.url" class="card-img-top" alt="Activity Image" v-if="example.images && example.images.length">
                                <div class="card-body">
                                    <h5 class="card-title">{{ example['title'] }}</h5>
                                    <p class="card-text">{{ example['preview'] }}</p>
                                </div>
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </div>
        </div>
    </section>
</template>

<script>

import 'swiper/swiper-bundle.css';
export default {
    name: 'activities',
    props: [
        'lang',
        'travel_type_list',
        'travel_examples',
    ],
    data() {
        return {
            activeActivity: null,
            travelExamples: null,
        }
    },
    created: function () {
        console.log(this.travel_examples);
    },
    methods: {},
}
</script>

<style scoped>
.activity-btn {
    background: #fff;
    border: 2px solid #007BFF;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 600;
    color: #007BFF;
    transition: all 0.3s ease;
    cursor: pointer;
    text-align: center; /* Center the text */
    white-space: nowrap; /* Prevent text wrapping */
    flex-grow: 1; /* Allow buttons to grow */
}

.activity-btn:hover {
    background: #007BFF;
    color: #fff;
    transform: scale(1.05);
}

.flex {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Allow wrapping */
}

@media (max-width: 768px) {
    .flex {
        flex-direction: column;
    }
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: scale(1.05);
}

.card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 15px;
    text-align: center;
}

.card-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.card-text {
    font-size: 14px;
    color: #666;
}
</style>

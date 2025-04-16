<template>
    <section class="relative tf-widget-activities pd-main overflow-hidden">
        <div class="row col-lg-12 justify-content-center">
            <div class="clip-text">Activities</div>
            <div class="flex col-lg-8 my-2">
                <div v-for="travelType in travel_type_list" @click="activeActivity = travelType.id"
                     class="mx-2 activity-btn my-2">
                    {{ travelType.name }}
                </div>
            </div>
            <div class="col-lg-12 my-2">
                <div v-for="example in travel_examples[activeActivity]" :key="example.id">
                    <div class="card mx-2 my-2" :class="activeActivity === example.id ? 'active' : ''">
                        <img :src="example.images[0]?.url" class="card-img-top" alt="Activity Image"
                             v-if="example.images && example.images.length">
                        <img v-else src="/images/clip-text.jpg" class="card-img-top" alt="Default Image">
                        <div class="card-body">
                            <div class="fw-bold">{{ example['title'] }}</div>
                        </div>
                        <div class="footer">
                            <div>{{ example['dateFrom'] }} - {{ example['dateTo'] }}</div>
                        </div>
                        <div class="footer" style="margin-bottom: 15px;">
                            <div>
                                <img :src="example['members']['icon']" alt="Members Icon" class="img-fluid"
                                     style="width: 20px; height: 20px;">
                                {{ example['members']['maxMember'] }}
                            </div>
                            <div>{{ example['country']['name'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
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

@media (max-width: 768px) {
    .flex {
        flex-direction: column;
    }
}

.col-lg-12.my-2 {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1px;
}

.card {
    display: flex;
    vertical-align: top;
    width: 300px;
    height: 400px;
    margin: 10px;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-align: left;
}

.card:hover {
    transform: scale(1.05);
}

.card-img-top {
    border-radius: 8px 8px 0 0;
    height: 200px;
    object-fit: cover;
    width: 100%;
}

.footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    margin-right: 10px;
    margin-left: 10px;
}
</style>

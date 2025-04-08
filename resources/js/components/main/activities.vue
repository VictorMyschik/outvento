<template>
    <section class="relative tf-widget-activities pd-main overflow-hidden">
        <div class="tf-container">
            <div class="row z-index3 relative ">
                <div class="col-lg-12 mb-60">
                    <div class="clip-text">Activities</div>
                </div>
                <div class="flex col-lg-12 my-2">
                    <div v-for="travelType in activities" class="mx-2 activity-btn my-2">{{ travelType.name }}</div>
                </div>
                <div class="col-lg-12">
                    <div class="slider">

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
        'activities',
    ],
    data() {
        return {
            activeActivity: null,
        }
    },
    created: function () {
        console.log(this.activities);
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
</style>

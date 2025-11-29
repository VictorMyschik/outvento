<template>
    <li class="nav-item dropdown">
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
            :class="{ 'dropdown-menu-start': isMobile }"
            aria-labelledby="travelsDropdown">

            <!-- Если список пустой -->
            <li v-if="!list || list.length === 0" class="px-4 text-muted small">
                У вас пока нет путешествий
            </li>

            <!-- Список путешествий -->
            <li v-for="item in list" :key="item.id">
                <a class="dropdown-item px-4 d-flex justify-content-between align-items-center"
                   :href="buildLink(urlList['account.travel.page'], item.id)">
                    <span class="text-truncate me-3" style="max-width: 280px;">{{ item.title }}</span></a>
            </li>
        </ul>
    </li>
</template>

<script>
import mrp from './MrPopupForm.vue';

export default {
    components: {
        mrp,
    },
    name: "nav_bar",
    data() {
        return {
            list: null,
            isMobile: false,
            urlList: {
                "api.travel.list": "/api/travels/list",
                "account.travel.page": "/account/travel/{travel_id}/page",
            },
        }
    },
    created() {
        this.getUsersTravelList();
        this.checkIfMobile();
        window.addEventListener('resize', this.checkIfMobile);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.checkIfMobile);
    },
    methods: {
        checkIfMobile() {
            this.isMobile = window.innerWidth < 768; // до 768px — считаем мобильным
        },

        getUsersTravelList() {
            axios.post(this.urlList['api.travel.list']).then(response => {
                if (response.data.result === true) {
                    this.list = response.data.content;
                }
            });
        },

        buildLink(url, id) {
            return url.replace('{travel_id}', id);
        }
    }
}
</script>

<style scoped>
/* Красивый дропдаун с тенью и скруглениями */
.dropdown-menu {
    border-radius: 12px !important;
    min-width: 300px;
    max-height: 50vh;
    overflow-y: auto;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
}

/* На мобильных — меню прижато к правому краю и открывается вниз */
@media (max-width: 767px) {
    .dropdown-menu {
        position: fixed !important;
        top: 60px !important; /* под твоим навбаром */
        right: 10px !important;
        left: 10px !important;
        width: auto !important;
        min-width: unset !important;
        max-height: calc(100vh - 80px);
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
    }
}

/* Анимация появления */
.dropdown-item {
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #8bc1ff;
}
</style>

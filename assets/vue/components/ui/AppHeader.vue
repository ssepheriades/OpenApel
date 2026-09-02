<script setup lang="ts">
import { computed, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useAppStore } from '@/stores/app';

interface NavItem {
    label: string;
    to: { name: string };
    icon: string;
}

const allNavItems: NavItem[] = [
    { label: 'Accueil', to: { name: 'home' }, icon: 'school' },
    { label: 'Actualités', to: { name: 'news' }, icon: 'newspaper' },
    { label: 'Agenda', to: { name: 'agenda' }, icon: 'calendar-days' },
    { label: 'FAQ', to: { name: 'faq' }, icon: 'circle-question' },
    { label: 'Équipe', to: { name: 'team' }, icon: 'users' },
    { label: 'Contact', to: { name: 'contact' }, icon: 'envelope' },
];

const drawer = ref(false);

const appStore = useAppStore();
const logoUrl = computed(() => appStore.settings?.logoUrl ?? null);
const navItems = computed(() => allNavItems.filter((item) => appStore.isRouteVisible(item.to.name)));
</script>

<template>
    <v-app-bar color="primary" density="comfortable">
        <v-app-bar-nav-icon class="d-sm-none" @click="drawer = !drawer">
            <FontAwesomeIcon :icon="['fas', 'bars']" />
        </v-app-bar-nav-icon>

        <v-app-bar-title>
            <router-link :to="{ name: 'home' }" class="brand-link">
                <img v-if="logoUrl" :src="logoUrl" alt="" class="brand-logo mr-2" />
                <FontAwesomeIcon v-else :icon="['fas', 'school']" class="mr-2" />
                {{ appStore.siteName }}
            </router-link>
        </v-app-bar-title>

        <nav class="d-none d-sm-flex">
            <v-btn
                v-for="item in navItems"
                :key="item.label"
                :to="item.to"
                variant="text"
            >
                <FontAwesomeIcon :icon="['fas', item.icon]" class="mr-2" />
                {{ item.label }}
            </v-btn>
        </nav>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary class="d-sm-none">
        <v-list nav>
            <v-list-item
                v-for="item in navItems"
                :key="item.label"
                :to="item.to"
                :title="item.label"
                @click="drawer = false"
            >
                <template #prepend>
                    <FontAwesomeIcon :icon="['fas', item.icon]" class="mr-4" />
                </template>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<style scoped>
.brand-link {
    display: inline-flex;
    align-items: center;
    color: inherit;
    text-decoration: none;
}

.brand-logo {
    height: 2rem;
    width: auto;
}
</style>

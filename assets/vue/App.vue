<script setup lang="ts">
import AppHeader from '@/components/ui/AppHeader.vue';
import AppFooter from '@/components/ui/AppFooter.vue';
</script>

<template>
    <v-app>
        <AppHeader />

        <v-main>
            <router-view />
        </v-main>

        <AppFooter />
    </v-app>
</template>

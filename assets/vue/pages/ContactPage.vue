<script setup lang="ts">
import ContactForm from '@/components/ui/ContactForm.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';

const page = useAppStore().pageContent('contact');
</script>

<template>
    <div class="contact-page">
        <PageHero
            :title="page.title"
            :subtitle="page.subtitle ?? undefined"
        />

        <v-container class="py-12">
            <ContactForm />
        </v-container>
    </div>
</template>

<style scoped>
.contact-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}
</style>

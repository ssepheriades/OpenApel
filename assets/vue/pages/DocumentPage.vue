<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MarkdownContent from '@/components/ui/MarkdownContent.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';
import type { PageSlug } from '@/api/pages';

const route = useRoute();
const appStore = useAppStore();

const slug = computed<PageSlug>(() =>
    route.name === 'politique-de-confidentialite' ? 'politique-de-confidentialite' : 'mentions-legales',
);

const page = computed(() => appStore.pageContent(slug.value));
const hasBody = computed(() => Boolean(page.value.body?.trim()));
</script>

<template>
    <div class="document-page">
        <PageHero :title="page.title" />

        <v-container class="py-12">
            <article v-if="hasBody" class="document-letter">
                <MarkdownContent :source="page.body ?? ''" />
            </article>
            <p v-else class="text-body-1 text-medium-emphasis text-center">
                Cette page sera bientôt complétée.
            </p>
        </v-container>
    </div>
</template>

<style scoped>
.document-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.document-letter {
    max-width: 42rem;
    margin: 0 auto;
    padding: 2.5rem 2.25rem;
    background: #fff;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    border-left: 4px solid rgb(var(--v-theme-secondary));
    box-shadow: 0 18px 50px -24px rgba(var(--v-theme-primary), 0.45);
}

@media (max-width: 600px) {
    .document-letter {
        padding: 1.6rem 1.25rem;
    }
}
</style>

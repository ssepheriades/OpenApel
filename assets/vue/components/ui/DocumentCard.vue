<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import type { SiteDocument } from '@/api/documents';
import ThemeChip from '@/components/ui/ThemeChip.vue';
import { formatPostDate } from '@/utils/postDate';

defineProps<{
    item: SiteDocument;
}>();
</script>

<template>
    <v-card class="document-card" elevation="0">
        <v-card-text class="document-card__body">
            <div class="document-card__meta">
                <ThemeChip :theme="item.theme" />
                <time class="document-card__date" :datetime="item.date">
                    {{ formatPostDate(item.date) }}
                </time>
            </div>
            <h2 class="document-card__title">{{ item.name }}</h2>
            <p v-if="item.description" class="document-card__excerpt">{{ item.description }}</p>
            <v-btn
                v-if="item.fileUrl"
                :href="item.fileUrl"
                target="_blank"
                rel="noopener noreferrer"
                color="primary"
                variant="flat"
                class="document-card__download mt-2"
            >
                <FontAwesomeIcon :icon="['fas', 'download']" class="mr-2" />
                Télécharger
            </v-btn>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.document-card {
    height: 100%;
    overflow: hidden;
    border-radius: 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    background: #fff;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.document-card:hover,
.document-card:focus-within {
    transform: translateY(-6px);
    border-color: rgba(var(--v-theme-secondary), 0.55);
    box-shadow: 0 18px 36px -18px rgba(var(--v-theme-primary), 0.4);
}

.document-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.35rem 1.3rem 1.5rem;
}

.document-card__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.document-card__date {
    font-size: 0.8rem;
    color: #5a5a6c;
    white-space: nowrap;
}

.document-card__title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.35;
    color: rgb(var(--v-theme-primary));
    letter-spacing: 0.01em;
}

.document-card__excerpt {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #5a5a6c;
}

.document-card__download {
    align-self: flex-start;
}
</style>

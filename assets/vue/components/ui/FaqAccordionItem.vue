<script setup lang="ts">
import type { Faq } from '@/api/faqs';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import ThemeChip from '@/components/ui/ThemeChip.vue';

defineProps<{ faq: Faq }>();
</script>

<template>
    <v-expansion-panel :value="faq.id">
        <v-expansion-panel-title class="text-primary font-weight-medium">
            <div class="faq-title">
                <span class="faq-title__question">{{ faq.question }}</span>
                <div class="faq-title__chips">
                    <ThemeChip :theme="faq.theme" />
                    <AudienceChips :grades="faq.grades" :school-classes="faq.schoolClasses" />
                </div>
            </div>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
            <p class="faq-answer">{{ faq.answer }}</p>
        </v-expansion-panel-text>
    </v-expansion-panel>
</template>

<style scoped>
.faq-title {
    display: flex;
    flex: 1;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem 0.85rem;
    min-width: 0;
    padding-right: 0.5rem;
}

.faq-title__question {
    flex: 1 1 12rem;
    min-width: 0;
}

.faq-title__chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
}

.faq-answer {
    white-space: pre-wrap;
    margin: 0;
}
</style>

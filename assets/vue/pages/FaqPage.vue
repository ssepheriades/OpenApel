<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fetchFaqs, type Faq } from '@/api/faqs';
import { THEME_LABELS } from '@/api/themes';
import FaqAccordionItem from '@/components/ui/FaqAccordionItem.vue';
import PageHero from '@/components/ui/PageHero.vue';

const faqs = ref<Faq[]>([]);
const query = ref('');
const isLoading = ref(true);
const error = ref<string | null>(null);

function normalize(value: string): string {
    return value
        .normalize('NFD')
        .replace(/\p{M}/gu, '')
        .toLocaleLowerCase('fr');
}

const filteredFaqs = computed(() => {
    const needle = normalize(query.value.trim());
    if (needle === '') {
        return faqs.value;
    }

    return faqs.value.filter((faq) => {
        const audienceNames = [
            ...(faq.grades ?? []).map((grade) => grade.name),
            ...(faq.schoolClasses ?? []).map((schoolClass) => schoolClass.name),
        ].join(' ');
        const haystack = `${faq.question} ${faq.answer} ${THEME_LABELS[faq.theme]} ${audienceNames}`;

        return normalize(haystack).includes(needle);
    });
});

onMounted(async () => {
    try {
        faqs.value = await fetchFaqs();
    } catch {
        error.value = 'Impossible de charger la FAQ pour le moment.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="faq-page">
        <PageHero
            title="FAQ"
            subtitle="Les questions les plus fréquentes des familles"
        />

        <v-container class="py-12">
            <div v-if="isLoading" class="text-center py-12">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <p class="mt-4">Chargement de la FAQ...</p>
            </div>

            <v-alert v-else-if="error" type="error" variant="tonal" :text="error"></v-alert>

            <div v-else-if="faqs.length === 0" class="text-center py-12">
                <FontAwesomeIcon :icon="['fas', 'circle-question']" size="3x" class="mb-4" />
                <p class="text-h6">Aucune question pour le moment</p>
            </div>

            <div v-else class="faq-panels">
                <v-text-field
                    v-model="query"
                    label="Rechercher une question"
                    placeholder="Mot-clé dans une question ou une réponse"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    clearable
                    class="mb-8"
                >
                    <template #prepend-inner>
                        <FontAwesomeIcon :icon="['fas', 'magnifying-glass']" class="text-medium-emphasis" />
                    </template>
                </v-text-field>

                <div v-if="filteredFaqs.length === 0" class="text-center py-12">
                    <FontAwesomeIcon :icon="['fas', 'magnifying-glass']" size="3x" class="mb-4" />
                    <p class="text-h6">Aucun résultat</p>
                    <p class="text-body2 text-medium-emphasis">Essayez un autre mot-clé.</p>
                </div>

                <v-expansion-panels v-else variant="accordion" class="faq-list">
                    <FaqAccordionItem v-for="faq in filteredFaqs" :key="faq.id" :faq="faq" />
                </v-expansion-panels>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.faq-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.faq-panels {
    max-width: 800px;
    margin: 0 auto;
}

.faq-list :deep(.v-expansion-panel) {
    margin-bottom: 0.85rem;
    overflow: hidden;
    border-radius: 16px !important;
}

.faq-list :deep(.v-expansion-panel:last-child) {
    margin-bottom: 0;
}
</style>

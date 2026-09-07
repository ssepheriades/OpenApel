<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fetchDocuments, type SiteDocument } from '@/api/documents';
import { fetchContentThemes, type ContentTheme } from '@/api/themes';
import DocumentCard from '@/components/ui/DocumentCard.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';

type ThemeFilter = 'all' | number;

const documents = ref<SiteDocument[]>([]);
const themes = ref<ContentTheme[]>([]);
const selectedTheme = ref<ThemeFilter>('all');
const isLoading = ref(true);
const error = ref<string | null>(null);
const page = useAppStore().pageContent('documents');

const filteredDocuments = computed(() => {
    if (selectedTheme.value === 'all') {
        return documents.value;
    }

    const themeId = Number(selectedTheme.value);

    return documents.value.filter((item) => item.theme.id === themeId);
});

onMounted(async () => {
    try {
        const [loadedDocuments, loadedThemes] = await Promise.all([
            fetchDocuments(),
            fetchContentThemes(),
        ]);
        documents.value = loadedDocuments;
        themes.value = loadedThemes;
    } catch {
        error.value = 'Impossible de charger les documents pour le moment.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="documents-page">
        <PageHero
            :title="page.title"
            :subtitle="page.subtitle ?? undefined"
        />

        <v-container class="py-12">
            <div v-if="isLoading" class="text-center py-12">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <p class="mt-4">Chargement des documents...</p>
            </div>

            <v-alert v-else-if="error" type="error" variant="tonal" :text="error"></v-alert>

            <div v-else-if="documents.length === 0" class="text-center py-12">
                <FontAwesomeIcon :icon="['fas', 'file']" size="3x" class="mb-4" />
                <p class="text-h6">Aucun document pour le moment</p>
            </div>

            <div v-else>
                <v-chip-group v-model="selectedTheme" class="mb-8" mandatory selected-class="text-primary">
                    <v-chip value="all" filter variant="outlined">Tous</v-chip>
                    <v-chip
                        v-for="theme in themes"
                        :key="theme.id"
                        :value="theme.id"
                        filter
                        variant="outlined"
                        :prepend-icon="theme.icon"
                    >
                        {{ theme.name }}
                    </v-chip>
                </v-chip-group>

                <div v-if="filteredDocuments.length === 0" class="text-center py-12">
                    <FontAwesomeIcon :icon="['fas', 'file']" size="3x" class="mb-4" />
                    <p class="text-h6">Aucun document dans ce thème</p>
                    <p class="text-body2 text-medium-emphasis">Essayez un autre filtre.</p>
                </div>

                <div v-else class="documents-grid">
                    <DocumentCard
                        v-for="item in filteredDocuments"
                        :key="item.id"
                        :item="item"
                    />
                </div>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.documents-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
</style>

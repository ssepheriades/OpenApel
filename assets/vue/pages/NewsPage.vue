<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fetchPosts, type Post } from '@/api/posts';
import { CONTENT_THEMES, THEME_ICONS, THEME_LABELS, type ContentTheme } from '@/api/themes';
import PageHero from '@/components/ui/PageHero.vue';
import PostCard from '@/components/ui/PostCard.vue';

type ThemeFilter = 'all' | ContentTheme;

const posts = ref<Post[]>([]);
const selectedTheme = ref<ThemeFilter>('all');
const isLoading = ref(true);
const error = ref<string | null>(null);

const filteredPosts = computed(() => {
    if (selectedTheme.value === 'all') {
        return posts.value;
    }

    return posts.value.filter((post) => post.theme === selectedTheme.value);
});

onMounted(async () => {
    try {
        posts.value = await fetchPosts();
    } catch {
        error.value = 'Impossible de charger les actualités pour le moment.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="news-page">
        <PageHero
            title="Actualités"
            subtitle="Les nouvelles de l'association"
        />

        <v-container class="py-12">
            <div v-if="isLoading" class="text-center py-12">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <p class="mt-4">Chargement des actualités...</p>
            </div>

            <v-alert v-else-if="error" type="error" variant="tonal" :text="error"></v-alert>

            <div v-else-if="posts.length === 0" class="text-center py-12">
                <FontAwesomeIcon :icon="['fas', 'newspaper']" size="3x" class="mb-4" />
                <p class="text-h6">Aucune actualité pour le moment</p>
            </div>

            <div v-else>
                <v-chip-group v-model="selectedTheme" class="mb-8" mandatory selected-class="text-primary">
                    <v-chip value="all" filter variant="outlined">Tous</v-chip>
                    <v-chip
                        v-for="theme in CONTENT_THEMES"
                        :key="theme"
                        :value="theme"
                        filter
                        variant="outlined"
                        :prepend-icon="THEME_ICONS[theme]"
                    >
                        {{ THEME_LABELS[theme] }}
                    </v-chip>
                </v-chip-group>

                <div v-if="filteredPosts.length === 0" class="text-center py-12">
                    <FontAwesomeIcon :icon="['fas', 'newspaper']" size="3x" class="mb-4" />
                    <p class="text-h6">Aucune actualité dans ce thème</p>
                    <p class="text-body2 text-medium-emphasis">Essayez un autre filtre.</p>
                </div>

                <div v-else class="news-grid">
                    <PostCard v-for="post in filteredPosts" :key="post.id" :post="post" />
                </div>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.news-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
</style>

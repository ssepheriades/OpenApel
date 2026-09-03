<script setup lang="ts">
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { ApiError } from '@/api/client';
import { fetchPost, type Post } from '@/api/posts';
import MarkdownContent from '@/components/ui/MarkdownContent.vue';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import PageHero from '@/components/ui/PageHero.vue';
import ThemeChip from '@/components/ui/ThemeChip.vue';
import { formatPostDate } from '@/utils/postDate';
import { useAppStore } from '@/stores/app';

const route = useRoute();
const page = useAppStore().pageContent('news');
const post = ref<Post | null>(null);
const isLoading = ref(true);
const error = ref<'load' | 'not-found' | null>(null);

async function load(): Promise<void> {
    isLoading.value = true;
    error.value = null;
    post.value = null;

    const id = Number(route.params.id);
    if (!Number.isInteger(id) || id < 1) {
        error.value = 'not-found';
        isLoading.value = false;

        return;
    }

    try {
        post.value = await fetchPost(id);
    } catch (cause) {
        error.value = cause instanceof ApiError && cause.status === 404 ? 'not-found' : 'load';
    } finally {
        isLoading.value = false;
    }
}

watch(() => route.params.id, load, { immediate: true });
</script>

<template>
    <div class="news-detail-page">
        <PageHero :title="page.title" />

        <v-container class="py-12">
            <div class="news-detail">
                <v-btn :to="{ name: 'news' }" variant="text" class="news-detail__back mb-6" color="primary">
                    <FontAwesomeIcon :icon="['fas', 'chevron-left']" class="mr-2" />
                    Toutes les actualités
                </v-btn>

                <div v-if="isLoading" class="text-center py-12">
                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                    <p class="mt-4">Chargement de l'actualité...</p>
                </div>

                <v-alert
                    v-else-if="error === 'load'"
                    type="error"
                    variant="tonal"
                    text="Impossible de charger cette actualité pour le moment."
                ></v-alert>

                <div v-else-if="error === 'not-found'" class="text-center py-12">
                    <FontAwesomeIcon :icon="['fas', 'circle-exclamation']" size="3x" class="mb-4" />
                    <p class="text-h6">Actualité introuvable</p>
                    <p class="text-body2 text-medium-emphasis">
                        Elle n'existe pas ou n'est plus publiée.
                    </p>
                </div>

                <article v-else-if="post" class="news-article">
                    <div class="news-article__meta">
                        <ThemeChip :theme="post.theme" />
                        <AudienceChips :grades="post.grades" :school-classes="post.schoolClasses" />
                        <time :datetime="post.createdAt">{{ formatPostDate(post.createdAt) }}</time>
                    </div>
                    <h1 class="news-article__title">{{ post.title }}</h1>
                    <MarkdownContent :source="post.content" />
                </article>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.news-detail-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.news-detail {
    max-width: 42rem;
    margin: 0 auto;
}

.news-detail__back {
    padding-inline: 0;
}

.news-article {
    padding: 2.25rem 2rem;
    background: #fff;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    border-left: 4px solid rgb(var(--v-theme-secondary));
    box-shadow: 0 18px 50px -24px rgba(var(--v-theme-primary), 0.45);
}

.news-article__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1rem;
}

.news-article__meta time {
    font-size: 0.875rem;
    color: #5a5a6c;
}

.news-article__title {
    margin: 0 0 1.25rem;
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.25;
    color: rgb(var(--v-theme-primary));
    letter-spacing: -0.02em;
}

@media (max-width: 600px) {
    .news-article {
        padding: 1.5rem 1.15rem;
    }

    .news-article__title {
        font-size: 1.5rem;
    }
}
</style>

<script setup lang="ts">
import { computed } from 'vue';
import type { Post } from '@/api/posts';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import ThemeChip from '@/components/ui/ThemeChip.vue';
import { excerptFromMarkdown } from '@/utils/markdown';
import { formatPostDate } from '@/utils/postDate';

const props = defineProps<{
    post: Post;
}>();

const excerpt = computed(() => excerptFromMarkdown(props.post.content));
</script>

<template>
    <v-card
        class="post-card"
        :to="{ name: 'news-detail', params: { id: post.id } }"
        elevation="0"
    >
        <v-card-text class="post-card__body">
            <div class="post-card__meta">
                <ThemeChip :theme="post.theme" />
                <time class="post-card__date" :datetime="post.createdAt">
                    {{ formatPostDate(post.createdAt) }}
                </time>
            </div>
            <AudienceChips :grades="post.grades" :school-classes="post.schoolClasses" />
            <h2 class="post-card__title">{{ post.title }}</h2>
            <p v-if="excerpt" class="post-card__excerpt">{{ excerpt }}</p>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.post-card {
    height: 100%;
    border-radius: 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    background: #fff;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.post-card:hover,
.post-card:focus-visible {
    transform: translateY(-6px);
    border-color: rgba(var(--v-theme-secondary), 0.55);
    box-shadow: 0 18px 36px -18px rgba(var(--v-theme-primary), 0.4);
}

.post-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.35rem 1.3rem 1.5rem;
}

.post-card__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.post-card__date {
    font-size: 0.8rem;
    color: #5a5a6c;
    white-space: nowrap;
}

.post-card__title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.35;
    color: rgb(var(--v-theme-primary));
    letter-spacing: 0.01em;
}

.post-card__excerpt {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #5a5a6c;
}
</style>

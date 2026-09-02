<script setup lang="ts">
import { computed } from 'vue';
import { renderMarkdown } from '@/utils/markdown';

const props = defineProps<{
    source: string;
}>();

const html = computed(() => renderMarkdown(props.source));
</script>

<template>
    <div class="markdown-content" v-html="html"></div>
</template>

<style scoped>
.markdown-content {
    font-size: 1.125rem;
    line-height: 1.75;
    color: #2a2a3a;
}

.markdown-content :deep(> *:first-child) {
    margin-top: 0;
}

.markdown-content :deep(> *:last-child) {
    margin-bottom: 0;
}

.markdown-content :deep(p),
.markdown-content :deep(ul),
.markdown-content :deep(ol),
.markdown-content :deep(blockquote) {
    margin: 0 0 1.15em;
}

.markdown-content :deep(h1),
.markdown-content :deep(h2),
.markdown-content :deep(h3),
.markdown-content :deep(h4) {
    font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
    line-height: 1.25;
    margin: 1.5em 0 0.6em;
}

.markdown-content :deep(a) {
    color: rgb(var(--v-theme-primary));
    text-decoration: underline;
    text-decoration-color: rgb(var(--v-theme-secondary));
    text-underline-offset: 3px;
}

.markdown-content :deep(a:hover) {
    color: rgb(var(--v-theme-secondary));
}

.markdown-content :deep(ul),
.markdown-content :deep(ol) {
    padding-left: 1.35em;
}

.markdown-content :deep(li + li) {
    margin-top: 0.35em;
}

.markdown-content :deep(blockquote) {
    border-left: 3px solid rgb(var(--v-theme-secondary));
    margin-left: 0;
    padding: 0.2em 0 0.2em 1.1em;
    color: #4a4a5c;
    font-style: italic;
}

.markdown-content :deep(code) {
    font-size: 0.9em;
    background: rgba(var(--v-theme-primary), 0.06);
    padding: 0.15em 0.4em;
    border-radius: 4px;
}

.markdown-content :deep(strong) {
    font-weight: 700;
    color: rgb(var(--v-theme-primary));
}
</style>

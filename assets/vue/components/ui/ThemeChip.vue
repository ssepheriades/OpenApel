<script setup lang="ts">
import { THEME_ICONS, THEME_LABELS, type ContentTheme } from '@/api/themes';

defineProps<{
    theme: ContentTheme;
}>();
</script>

<template>
    <v-chip size="small" variant="tonal" color="primary" :prepend-icon="THEME_ICONS[theme]">
        {{ THEME_LABELS[theme] }}
    </v-chip>
</template>

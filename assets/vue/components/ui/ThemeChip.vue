<script setup lang="ts">
import type { ContentTheme } from '@/api/themes';

defineProps<{
    theme: ContentTheme;
}>();
</script>

<template>
    <v-chip size="small" variant="tonal" color="primary" :prepend-icon="theme.icon">
        {{ theme.name }}
    </v-chip>
</template>

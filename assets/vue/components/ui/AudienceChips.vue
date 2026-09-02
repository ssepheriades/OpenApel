<script setup lang="ts">
import { computed } from 'vue';
import type { Grade, SchoolClass } from '@/api/audience';

const props = withDefaults(
    defineProps<{
        grades?: Grade[];
        schoolClasses?: SchoolClass[];
    }>(),
    {
        grades: () => [],
        schoolClasses: () => [],
    },
);

const hasAudience = computed(
    () => props.grades.length > 0 || props.schoolClasses.length > 0,
);
</script>

<template>
    <div v-if="hasAudience" class="audience-chips">
        <v-chip
            v-for="grade in grades"
            :key="`grade-${grade.id}`"
            size="small"
            variant="tonal"
            color="secondary"
            prepend-icon="mdi-school"
        >
            {{ grade.name }}
        </v-chip>
        <v-chip
            v-for="schoolClass in schoolClasses"
            :key="`class-${schoolClass.id}`"
            size="small"
            variant="tonal"
            color="secondary"
            prepend-icon="mdi-account-group"
        >
            {{ schoolClass.name }}
        </v-chip>
    </div>
</template>

<style scoped>
.audience-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
}
</style>

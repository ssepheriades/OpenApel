<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useDisplay } from 'vuetify';
import type { Event } from '@/api/events';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import { formatEventDate, formatEventTime } from '@/utils/eventDate';

const props = withDefaults(
    defineProps<{
        event: Event;
        isPast?: boolean;
        anchorId?: string;
    }>(),
    {
        isPast: false,
    },
);

const { smAndDown } = useDisplay();
const eventTime = computed(() => formatEventTime(props.event));

const chipColor = computed(() => {
    if (props.isPast) {
        return 'grey';
    }

    if (props.event.state === 'cancelled') {
        return 'error';
    }

    if (props.event.state === 'full') {
        return 'warning';
    }

    return 'success';
});
</script>

<template>
    <v-timeline-item
        :dot-color="isPast ? 'grey' : 'primary'"
        size="small"
        class="event-timeline-item"
        :class="{ 'event-timeline-item--past': isPast }"
    >
        <template #opposite>
            <div class="event-when">
                <span class="event-when__date">{{ formatEventDate(event.startsAt) }}</span>
                <span v-if="eventTime" class="event-when__time">{{ eventTime }}</span>
                <span v-else-if="event.isAllDay" class="event-when__time">Journée</span>
            </div>
        </template>

        <v-card :id="anchorId" class="event-card">
            <v-card-title class="event-card__title">{{ event.title }}</v-card-title>
            <v-card-text>
                <p v-if="event.shortDescription">{{ event.shortDescription }}</p>
                <p v-if="smAndDown" class="event-meta">
                    <FontAwesomeIcon :icon="['fas', 'calendar-days']" class="event-meta__icon" />
                    <span>{{ formatEventDate(event.startsAt) }}</span>
                </p>
                <p v-if="eventTime" class="event-meta">
                    <FontAwesomeIcon :icon="['fas', 'clock']" class="event-meta__icon" />
                    <span>{{ eventTime }}</span>
                </p>
                <p v-else-if="smAndDown && event.isAllDay" class="event-meta">
                    <FontAwesomeIcon :icon="['fas', 'clock']" class="event-meta__icon" />
                    <span>Journée</span>
                </p>
                <p v-if="event.location" class="event-meta">
                    <FontAwesomeIcon :icon="['fas', 'location-dot']" class="event-meta__icon" />
                    <span>{{ event.location }}</span>
                </p>
                <AudienceChips
                    class="mt-3"
                    :grades="event.grades"
                    :school-classes="event.schoolClasses"
                />
                <v-chip
                    v-if="event.state === 'cancelled'"
                    class="mt-3"
                    size="small"
                    :color="chipColor"
                    variant="tonal"
                >
                    Annulé
                </v-chip>
                <v-chip
                    v-else-if="event.state === 'full'"
                    class="mt-3"
                    size="small"
                    :color="chipColor"
                    variant="tonal"
                >
                    Complet
                </v-chip>
                <v-chip
                    v-else
                    class="mt-3"
                    size="small"
                    :color="chipColor"
                    variant="tonal"
                >
                    Ouvert
                </v-chip>
            </v-card-text>
        </v-card>
    </v-timeline-item>
</template>

<style scoped>
.event-card {
    scroll-margin-top: 5rem;
}

.event-card__title {
    white-space: normal;
}

.event-when {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;
    line-height: 1.25;
}

.event-when__time {
    font-size: 0.8rem;
    font-variant-numeric: tabular-nums;
    opacity: 0.72;
    white-space: nowrap;
}

.event-meta {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin: 0 0 0.35rem;
}

.event-meta__icon {
    margin-top: 0.2rem;
    width: 0.85rem;
    color: rgb(var(--v-theme-primary));
    opacity: 0.85;
}

.event-timeline-item--past {
    opacity: 0.55;
}

.event-timeline-item--past .event-meta__icon {
    color: inherit;
}
</style>

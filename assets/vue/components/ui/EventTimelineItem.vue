<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useDisplay } from 'vuetify';
import { hasAgendaDetail, isSchoolClosureEvent, type Event } from '@/api/events';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import { formatEventDateRange, formatEventTime } from '@/utils/eventDate';

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
const dateLabel = computed(() => formatEventDateRange(props.event));
const isClosure = computed(() => isSchoolClosureEvent(props.event.type));
const isGreyed = computed(() => props.event.visibility === 'greyed_out');
const detailTo = computed(() => {
    if (isGreyed.value || !hasAgendaDetail(props.event.type)) {
        return undefined;
    }

    return { name: 'agenda-detail', params: { slug: props.event.slug } };
});

const chipColor = computed(() => {
    if (props.isPast || isGreyed.value) {
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
        :dot-color="isPast || isGreyed ? 'grey' : 'primary'"
        size="small"
        class="event-timeline-item"
        :class="{
            'event-timeline-item--past': isPast,
            'event-timeline-item--greyed': isGreyed,
            'event-timeline-item--closure': isClosure,
        }"
    >
        <template #opposite>
            <div class="event-when">
                <span class="event-when__date">{{ dateLabel }}</span>
                <template v-if="!isClosure">
                    <span v-if="eventTime" class="event-when__time">{{ eventTime }}</span>
                    <span v-else-if="event.isAllDay" class="event-when__time">Journée entière</span>
                </template>
            </div>
        </template>

        <v-card
            :id="anchorId"
            class="event-card"
            :class="{
                'event-card--link': !!detailTo,
                'event-card--closure': isClosure,
            }"
            v-bind="detailTo ? { to: detailTo } : {}"
            elevation="0"
        >
            <div class="event-card__row">
                <div class="event-card__body">
                    <v-card-title class="event-card__title">{{ event.title }}</v-card-title>
                    <v-card-text v-if="isClosure && smAndDown">
                        <p class="event-meta">
                            <FontAwesomeIcon :icon="['fas', 'calendar-days']" class="event-meta__icon" />
                            <span>{{ dateLabel }}</span>
                        </p>
                    </v-card-text>
                    <v-card-text v-else-if="!isClosure">
                        <p v-if="event.shortDescription">{{ event.shortDescription }}</p>
                        <p v-if="smAndDown" class="event-meta">
                            <FontAwesomeIcon :icon="['fas', 'calendar-days']" class="event-meta__icon" />
                            <span>{{ dateLabel }}</span>
                        </p>
                        <p v-if="eventTime" class="event-meta">
                            <FontAwesomeIcon :icon="['fas', 'clock']" class="event-meta__icon" />
                            <span>{{ eventTime }}</span>
                        </p>
                        <p v-else-if="smAndDown && event.isAllDay" class="event-meta">
                            <FontAwesomeIcon :icon="['fas', 'clock']" class="event-meta__icon" />
                            <span>Journée entière</span>
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
                </div>
                <v-img
                    v-if="!isClosure && event.flyerImageUrl"
                    :src="event.flyerImageUrl"
                    alt=""
                    class="event-card__flyer"
                    :width="smAndDown ? 56 : 72"
                    :height="smAndDown ? 72 : 96"
                    cover
                />
            </div>
        </v-card>
    </v-timeline-item>
</template>

<style scoped>
.event-card {
    width: 100%;
    scroll-margin-top: 5rem;
    overflow: hidden;
    border: 1px solid rgb(var(--v-theme-secondary));
}

.event-card--link {
    cursor: pointer;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
}

.event-card--link:hover,
.event-card--link:focus-visible {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px -18px rgba(var(--v-theme-primary), 0.45);
}

.event-card__row {
    display: flex;
    align-items: flex-start;
}

.event-card__body {
    flex: 1;
    min-width: 0;
}

.event-card__flyer {
    flex: 0 0 auto;
    margin: 0.7rem 0.7rem 0.7rem 0;
    overflow: hidden;
    border-radius: 8px;
}

.event-card__title {
    white-space: normal;
}

.event-card--closure {
    background-color: rgb(var(--v-theme-surface));
    background-image: repeating-linear-gradient(
        -45deg,
        rgba(var(--v-theme-secondary), 0.14) 0 10px,
        transparent 10px 20px
    );
    border-style: dashed;
}

.event-card--closure .event-card__title {
    padding-bottom: 0.75rem;
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

.event-timeline-item--past,
.event-timeline-item--greyed {
    opacity: 0.55;
}

.event-timeline-item--past .event-meta__icon,
.event-timeline-item--greyed .event-meta__icon {
    color: inherit;
}
</style>

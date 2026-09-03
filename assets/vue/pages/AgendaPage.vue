<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useDisplay } from 'vuetify';
import { fetchEvents, type Event } from '@/api/events';
import EventTimelineItem from '@/components/ui/EventTimelineItem.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';
import {
    DEFAULT_SCHOOL_YEAR_END,
    DEFAULT_SCHOOL_YEAR_START,
    findNextEventId,
    getSchoolYearRange,
    isUpcomingEvent,
    parseMonthDay,
    toIsoDate,
} from '@/utils/schoolYear';

const appStore = useAppStore();
const page = appStore.pageContent('agenda');
const { smAndDown } = useDisplay();
const events = ref<Event[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

const nextEventId = computed(() => findNextEventId(events.value));

function isPastEvent(event: Event): boolean {
    return !isUpcomingEvent(event);
}

watch(
    [isLoading, nextEventId],
    async ([loading, id]) => {
        if (loading || id === null) {
            return;
        }

        await nextTick();
        requestAnimationFrame(() => {
            const el = document.getElementById('agenda-next-event');
            if (!el) {
                return;
            }

            const headerOffset = 80;
            const top = el.getBoundingClientRect().top + window.scrollY - headerOffset;
            window.scrollTo({ top: Math.max(0, top), behavior: 'auto' });
        });
    },
    { flush: 'post' },
);

onMounted(async () => {
    try {
        const startMd = parseMonthDay(appStore.settings?.schoolYearStart, DEFAULT_SCHOOL_YEAR_START);
        const endMd = parseMonthDay(appStore.settings?.schoolYearEnd, DEFAULT_SCHOOL_YEAR_END);
        const range = getSchoolYearRange(new Date(), startMd, endMd);

        events.value = await fetchEvents({
            after: toIsoDate(range.start),
            strictlyBefore: toIsoDate(range.endExclusive),
        });
    } catch {
        error.value = "Impossible de charger l'agenda pour le moment.";
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="agenda-page">
        <PageHero
            :title="page.title"
            :subtitle="page.subtitle ?? undefined"
        />

        <v-container class="py-12">
            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-12">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <p class="mt-4">Chargement de l'agenda...</p>
            </div>

            <!-- Error State -->
            <v-alert v-else-if="error" type="error" variant="tonal" :text="error"></v-alert>

            <!-- Empty State -->
            <div v-else-if="events.length === 0" class="text-center py-12">
                <FontAwesomeIcon icon="calendar-days" size="3x" class="mb-4" />
                <p class="text-h6">Aucun événement pour le moment</p>
            </div>

            <!-- Timeline -->
            <v-timeline v-else side="end" :density="smAndDown ? 'compact' : 'comfortable'">
                <EventTimelineItem
                    v-for="event in events"
                    :key="event.id"
                    :event="event"
                    :is-past="isPastEvent(event)"
                    :anchor-id="event.id === nextEventId ? 'agenda-next-event' : undefined"
                />
            </v-timeline>
        </v-container>
    </div>
</template>

<style scoped>
.agenda-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

:deep(#agenda-next-event) {
    scroll-margin-top: 5rem;
}
</style>

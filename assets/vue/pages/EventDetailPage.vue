<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { ApiError } from '@/api/client';
import { fetchEvent, type Event } from '@/api/events';
import AudienceChips from '@/components/ui/AudienceChips.vue';
import MarkdownContent from '@/components/ui/MarkdownContent.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';
import { formatEventDateRange, formatEventTime } from '@/utils/eventDate';

const route = useRoute();
const page = useAppStore().pageContent('agenda');
const event = ref<Event | null>(null);
const isLoading = ref(true);
const error = ref<'load' | 'not-found' | null>(null);
const flyerOpen = ref(false);

const dateLabel = computed(() => (event.value ? formatEventDateRange(event.value) : ''));
const timeLabel = computed(() => (event.value ? formatEventTime(event.value) : null));

const stateChip = computed(() => {
    if (!event.value) {
        return null;
    }

    if (event.value.state === 'cancelled') {
        return { label: 'Annulé', color: 'error' };
    }

    if (event.value.state === 'full') {
        return { label: 'Complet', color: 'warning' };
    }

    return { label: 'Ouvert', color: 'success' };
});

async function load(): Promise<void> {
    isLoading.value = true;
    error.value = null;
    event.value = null;
    flyerOpen.value = false;

    const id = Number(route.params.id);
    if (!Number.isInteger(id) || id < 1) {
        error.value = 'not-found';
        isLoading.value = false;

        return;
    }

    try {
        event.value = await fetchEvent(id);
    } catch (cause) {
        error.value = cause instanceof ApiError && cause.status === 404 ? 'not-found' : 'load';
    } finally {
        isLoading.value = false;
    }
}

watch(() => route.params.id, load, { immediate: true });
</script>

<template>
    <div class="event-detail-page">
        <PageHero :title="page.title" />

        <v-container class="py-12">
            <div class="event-detail">
                <v-btn :to="{ name: 'agenda' }" variant="text" class="event-detail__back mb-6" color="primary">
                    <FontAwesomeIcon :icon="['fas', 'chevron-left']" class="mr-2" />
                    Tout l'agenda
                </v-btn>

                <div v-if="isLoading" class="text-center py-12">
                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                    <p class="mt-4">Chargement de l'événement...</p>
                </div>

                <v-alert
                    v-else-if="error === 'load'"
                    type="error"
                    variant="tonal"
                    text="Impossible de charger cet événement pour le moment."
                ></v-alert>

                <div v-else-if="error === 'not-found'" class="text-center py-12">
                    <FontAwesomeIcon :icon="['fas', 'circle-exclamation']" size="3x" class="mb-4" />
                    <p class="text-h6">Événement introuvable</p>
                    <p class="text-body2 text-medium-emphasis">
                        Il n'existe pas ou n'est plus publié.
                    </p>
                </div>

                <article v-else-if="event" class="event-article">
                    <v-img
                        v-if="event.heroImageUrl"
                        :src="event.heroImageUrl"
                        alt=""
                        class="event-article__hero"
                        height="220"
                        cover
                    />

                    <div class="event-article__panel" :class="{ 'event-article__panel--with-hero': Boolean(event.heroImageUrl) }">
                        <div class="event-article__headline">
                            <button
                                v-if="event.flyerImageUrl"
                                type="button"
                                class="event-article__flyer"
                                aria-label="Agrandir le flyer"
                                @click="flyerOpen = true"
                            >
                                <v-img :src="event.flyerImageUrl" alt="" cover />
                            </button>
                            <h1 class="event-article__title">{{ event.title }}</h1>
                        </div>

                        <div class="event-article__meta">
                            <p class="event-article__fact">
                                <FontAwesomeIcon :icon="['fas', 'calendar-days']" class="event-article__icon" />
                                <span>{{ dateLabel }}</span>
                            </p>
                            <p v-if="timeLabel" class="event-article__fact">
                                <FontAwesomeIcon :icon="['fas', 'clock']" class="event-article__icon" />
                                <span>{{ timeLabel }}</span>
                            </p>
                            <p v-else-if="event.isAllDay" class="event-article__fact">
                                <FontAwesomeIcon :icon="['fas', 'clock']" class="event-article__icon" />
                                <span>Journée entière</span>
                            </p>
                            <p v-if="event.location" class="event-article__fact">
                                <FontAwesomeIcon :icon="['fas', 'location-dot']" class="event-article__icon" />
                                <span>{{ event.location }}</span>
                            </p>
                            <v-chip
                                v-if="stateChip"
                                size="small"
                                :color="stateChip.color"
                                variant="tonal"
                            >
                                {{ stateChip.label }}
                            </v-chip>
                            <AudienceChips :grades="event.grades" :school-classes="event.schoolClasses" />
                        </div>

                        <MarkdownContent v-if="event.description" :source="event.description" />

                        <v-btn
                            v-if="event.ticketingUrl"
                            :href="event.ticketingUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            color="primary"
                            class="mt-6"
                        >
                            <FontAwesomeIcon :icon="['fas', 'ticket']" class="mr-2" />
                            Billetterie
                        </v-btn>
                    </div>
                </article>
            </div>
        </v-container>

        <v-dialog v-model="flyerOpen" max-width="900">
            <v-img v-if="event?.flyerImageUrl" :src="event.flyerImageUrl" alt="" />
        </v-dialog>
    </div>
</template>

<style scoped>
.event-detail-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.event-detail {
    max-width: 48rem;
    margin: 0 auto;
}

.event-detail__back {
    padding-inline: 0;
}

.event-article {
    overflow: hidden;
    background: #fff;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    border-left: 4px solid rgb(var(--v-theme-secondary));
    box-shadow: 0 18px 50px -24px rgba(var(--v-theme-primary), 0.45);
}

.event-article__hero {
    width: 100%;
}

.event-article__panel {
    padding: 1.75rem 2rem 2.25rem;
}

.event-article__panel--with-hero {
    padding-top: 0;
}

.event-article__headline {
    display: flex;
    align-items: flex-end;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.event-article__panel--with-hero .event-article__headline {
    margin-top: -3.5rem;
}

.event-article__flyer {
    flex-shrink: 0;
    width: 9.5rem;
    height: 12.5rem;
    padding: 0;
    overflow: hidden;
    border: 3px solid #fff;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 10px 24px -12px rgba(15, 23, 42, 0.55);
    cursor: zoom-in;
}

.event-article__title {
    margin: 0 0 0.35rem;
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.25;
    color: rgb(var(--v-theme-primary));
    letter-spacing: -0.02em;
}

.event-article__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1.1rem;
    margin-bottom: 1.5rem;
}

.event-article__fact {
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
    margin: 0;
    font-size: 0.95rem;
    color: #5a5a6c;
}

.event-article__icon {
    margin-top: 0.2rem;
    width: 0.85rem;
    color: rgb(var(--v-theme-primary));
}

@media (max-width: 600px) {
    .event-article__panel {
        padding: 1.35rem 1.15rem 1.75rem;
    }

    .event-article__headline {
        flex-direction: column;
        align-items: flex-start;
    }

    .event-article__panel--with-hero .event-article__headline {
        margin-top: -2.5rem;
    }

    .event-article__flyer {
        width: 8rem;
        height: 10.5rem;
    }

    .event-article__title {
        font-size: 1.5rem;
    }
}
</style>

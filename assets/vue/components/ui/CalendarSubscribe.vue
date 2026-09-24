<script setup lang="ts">
import { computed, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    CALENDAR_FEED_PATH,
    calendarFeedUrl,
    googleCalendarSubscribeUrl,
    webcalUrl,
} from '@/utils/calendar';

const dialogOpen = ref(false);
const copied = ref(false);
const copyFailed = ref(false);

const feedUrl = computed(() => calendarFeedUrl());
const subscribeUrl = computed(() => webcalUrl(feedUrl.value));
const googleUrl = computed(() => googleCalendarSubscribeUrl(feedUrl.value));

async function copyFeedUrl(): Promise<void> {
    copyFailed.value = false;
    try {
        await navigator.clipboard.writeText(feedUrl.value);
        copied.value = true;
    } catch {
        copyFailed.value = true;
    }
}
</script>

<template>
    <div class="calendar-subscribe">
        <v-btn color="primary" variant="outlined" @click="dialogOpen = true">
            <FontAwesomeIcon :icon="['fas', 'calendar-plus']" class="mr-2" />
            S'abonner à l'agenda
        </v-btn>

        <v-dialog v-model="dialogOpen" max-width="32rem">
            <v-card rounded="lg">
                <v-card-title class="text-wrap pt-6 px-6">S'abonner à l'agenda</v-card-title>
                <v-card-text class="px-6">
                    <p class="mb-2">
                        Les fêtes, réunions, journées pédagogiques et événements scolaires
                        seront ajoutés à votre calendrier et se mettront à jour (nouvelles
                        dates, reports, annulations).
                    </p>
                    <p class="text-medium-emphasis mb-6">
                        Les vacances et jours fériés ne sont pas inclus.
                    </p>

                    <div class="calendar-subscribe__actions">
                        <v-btn :href="subscribeUrl" color="primary" variant="flat">
                            <FontAwesomeIcon :icon="['fas', 'calendar-plus']" class="mr-2" />
                            Apple / Outlook
                        </v-btn>
                        <v-btn
                            :href="googleUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            color="primary"
                            variant="outlined"
                        >
                            <FontAwesomeIcon :icon="['fas', 'arrow-up-right-from-square']" class="mr-2" />
                            Google Agenda
                        </v-btn>
                        <v-btn color="primary" variant="outlined" @click="copyFeedUrl">
                            <FontAwesomeIcon :icon="['fas', 'copy']" class="mr-2" />
                            Copier le lien
                        </v-btn>
                        <v-btn :href="CALENDAR_FEED_PATH" color="primary" variant="text">
                            <FontAwesomeIcon :icon="['fas', 'download']" class="mr-2" />
                            Télécharger une copie
                        </v-btn>
                    </div>
                </v-card-text>
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="dialogOpen = false">Fermer</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="copied" timeout="2500" color="primary" location="top">Lien copié</v-snackbar>
        <v-snackbar v-model="copyFailed" timeout="3500" color="error" location="top">
            Impossible de copier le lien. Ajoutez-le manuellement depuis Google Agenda
            (Paramètres → Ajouter un agenda → À partir de l'URL).
        </v-snackbar>
    </div>
</template>

<style scoped>
.calendar-subscribe {
    display: flex;
    justify-content: center;
}

.calendar-subscribe__actions {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.calendar-subscribe__actions :deep(.v-btn) {
    justify-content: flex-start;
}
</style>

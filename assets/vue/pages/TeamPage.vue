<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fetchTeamMembers, type TeamMember } from '@/api/team';
import TeamMemberCard from '@/components/ui/TeamMemberCard.vue';
import PageHero from '@/components/ui/PageHero.vue';
import { useAppStore } from '@/stores/app';

const page = useAppStore().pageContent('team');

const members = ref<TeamMember[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
    try {
        members.value = await fetchTeamMembers();
    } catch {
        error.value = "Impossible de charger l'équipe pour le moment.";
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="team-page">
        <PageHero :title="page.title" :subtitle="page.subtitle ?? undefined" />

        <!-- Content -->
        <v-container class="py-12">
            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-12">
                <v-progress-circular indeterminate color="secondary" size="60"></v-progress-circular>
                <p class="mt-6 text-secondary font-weight-medium">Chargement de l'équipe...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error">
                <v-alert type="error" variant="tonal" :text="error" closable></v-alert>
            </div>

            <!-- Empty State -->
            <div v-else-if="members.length === 0" class="text-center py-12">
                <FontAwesomeIcon icon="users" size="4x" class="text-primary mb-4" style="display: block; margin-bottom: 16px;" />
                <p class="text-h6 text-primary font-weight-bold">Aucun membre pour le moment</p>
                <p class="text-body2 text-disabled">L'équipe sera bientôt complétée</p>
            </div>

            <!-- Members Grid -->
            <div v-else class="members-grid">
                <div
                    v-for="member in members"
                    :key="member.id"
                    class="grid-item"
                >
                    <TeamMemberCard :member="member" />
                </div>
            </div>

            <!-- Members Count -->
            <div class="text-center mt-12">
                <v-chip color="primary" text-color="white" size="large">
                    <FontAwesomeIcon icon="user" class="mr-2" />
                    {{ members.length }} membre{{ members.length > 1 ? 's' : '' }}
                </v-chip>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.team-page {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
}

.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, 280px);
    gap: 32px;
    justify-content: center;
    max-width: 1400px;
    margin: 0 auto;
}

.grid-item {
    display: flex;
    width: 280px;
    max-width: 280px;
}

@media (max-width: 1024px) {
    .members-grid {
        grid-template-columns: repeat(auto-fill, 240px);
        gap: 24px;
    }

    .grid-item {
        width: 240px;
        max-width: 240px;
    }
}

@media (max-width: 768px) {
    .members-grid {
        grid-template-columns: repeat(2, 260px);
        gap: 20px;
    }

    .grid-item {
        width: 260px;
        max-width: 260px;
    }
}

@media (max-width: 600px) {
    .members-grid {
        grid-template-columns: 260px;
        gap: 16px;
    }

    .grid-item {
        width: 260px;
        max-width: 260px;
    }
}
</style>

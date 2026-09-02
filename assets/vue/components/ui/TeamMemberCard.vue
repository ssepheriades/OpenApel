<script setup lang="ts">
import type { TeamMember } from '@/api/team';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

defineProps<{ member: TeamMember }>();
</script>

<template>
    <v-card class="material-card h-100" elevation="0">
        <!-- Image container with overlay -->
        <div class="card-image-container">
            <v-img
                v-if="member.photoUrl"
                :src="member.photoUrl"
                :alt="`${member.firstName} ${member.lastName}`"
                cover
                height="280"
                class="card-image"
            />
            <div v-else class="card-image-placeholder">
                <FontAwesomeIcon icon="user" size="5x" class="text-secondary opacity-50" />
            </div>
            <!-- Gradient overlay -->
            <div class="card-overlay"></div>

            <!-- Position badge overlay -->
            <div v-if="member.position" class="position-overlay">
                {{ member.position }}
            </div>
        </div>

        <!-- Content section -->
        <div class="card-content">
            <h3 class="card-name">
                {{ member.firstName }}
                <span class="card-lastname">{{ member.lastName }}</span>
            </h3>
        </div>
    </v-card>
</template>

<style scoped>
.material-card {
    border-radius: 16px;
    overflow: hidden;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: 1px solid rgba(39, 40, 87, 0.08);
    width: 100%;
    height: 100%;
}

.material-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(39, 40, 87, 0.2), 0 8px 16px rgba(46, 216, 255, 0.12) !important;
}

.card-image-container {
    position: relative;
    width: 100%;
    height: 280px;
    overflow: hidden;
    background: linear-gradient(135deg, #272857 0%, #2ed8ff 100%);
}

.card-image {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.material-card:hover .card-image {
    transform: scale(1.08);
}

.card-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #272857 0%, #2ed8ff 100%);
}

.card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(to top, rgba(39, 40, 87, 0.4), transparent);
    pointer-events: none;
}

.position-overlay {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #2ed8ff;
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
    z-index: 10;
}

.card-content {
    padding: 24px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    min-height: 100px;
    background: white;
}

.card-name {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #272857;
    letter-spacing: 0.3px;
    line-height: 1.4;
}

.card-lastname {
    display: block;
    color: #2ed8ff;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

@media (max-width: 600px) {
    .card-image-container {
        height: 200px;
    }

    .card-content {
        padding: 16px 12px;
        min-height: 80px;
    }

    .card-name {
        font-size: 1.1rem;
    }
}
</style>

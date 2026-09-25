<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
    coverImageUrl?: string | null;
}>();
</script>

<template>
    <div
        class="hero-header"
        :class="{ 'hero-header--cover': Boolean(coverImageUrl) }"
        :style="coverImageUrl ? { backgroundImage: `url(${coverImageUrl})` } : undefined"
    >
        <div v-if="coverImageUrl" class="hero-overlay" aria-hidden="true"></div>
        <v-container>
            <div class="hero-content">
                <h1 class="hero-title">{{ title }}</h1>
                <p v-if="subtitle" class="hero-subtitle">{{ subtitle }}</p>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.hero-header {
    background: linear-gradient(
        118deg,
        rgb(var(--v-theme-primary)) 0%,
        rgb(var(--v-theme-primary)) 42%,
        rgb(var(--v-theme-secondary)) 100%
    );
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.hero-header--cover {
    background-color: rgb(var(--v-theme-primary));
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-header:not(.hero-header--cover)::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    opacity: 0.5;
}

.hero-header:not(.hero-header--cover)::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
    opacity: 0.5;
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0.55) 100%);
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    letter-spacing: -1px;
    margin-bottom: 16px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.hero-header--cover .hero-title,
.hero-header--cover .hero-subtitle {
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.45);
}

.hero-subtitle {
    font-size: 1.25rem;
    font-weight: 300;
    letter-spacing: 0.5px;
    max-width: 600px;
    margin: 0 auto;
    opacity: 0.95;
    line-height: 1.6;
}

@media (max-width: 600px) {
    .hero-header {
        padding: 50px 0;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }
}
</style>

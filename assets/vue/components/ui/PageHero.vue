<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
}>();
</script>

<template>
    <div class="hero-header">
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
    background: linear-gradient(135deg, #272857 0%, #2ed8ff 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.hero-header::before {
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

.hero-header::after {
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

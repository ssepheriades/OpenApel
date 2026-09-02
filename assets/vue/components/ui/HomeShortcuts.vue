<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useAppStore } from '@/stores/app';

interface Shortcut {
    label: string;
    description: string;
    to: { name: string };
    icon: string;
}

const appStore = useAppStore();

const allShortcuts: Shortcut[] = [
    {
        label: 'Actualités',
        description: "Les nouvelles de l'association",
        to: { name: 'news' },
        icon: 'newspaper',
    },
    {
        label: 'Équipe',
        description: "Les bénévoles de l'association",
        to: { name: 'team' },
        icon: 'users',
    },
    {
        label: 'Agenda',
        description: 'Les dates et événements à venir',
        to: { name: 'agenda' },
        icon: 'calendar-days',
    },
    {
        label: 'FAQ',
        description: 'Les questions des familles',
        to: { name: 'faq' },
        icon: 'circle-question',
    },
];

const shortcuts = computed(() => allShortcuts.filter((shortcut) => appStore.isRouteVisible(shortcut.to.name)));
</script>

<template>
    <nav class="home-shortcuts" aria-label="Rubriques du site">
        <router-link
            v-for="shortcut in shortcuts"
            :key="shortcut.label"
            :to="shortcut.to"
            class="home-shortcut"
        >
            <span class="home-shortcut__icon" aria-hidden="true">
                <FontAwesomeIcon :icon="['fas', shortcut.icon]" />
            </span>
            <span class="home-shortcut__body">
                <span class="home-shortcut__label">{{ shortcut.label }}</span>
                <span class="home-shortcut__desc">{{ shortcut.description }}</span>
            </span>
            <FontAwesomeIcon :icon="['fas', 'chevron-right']" class="home-shortcut__chevron" />
        </router-link>
    </nav>
</template>

<style scoped>
.home-shortcuts {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.home-shortcut {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.15rem 1.2rem;
    background: #fff;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    border-radius: 16px;
    color: inherit;
    text-decoration: none;
    box-shadow: 0 10px 28px -20px rgba(var(--v-theme-primary), 0.45);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.home-shortcut:hover,
.home-shortcut:focus-visible {
    transform: translateY(-6px);
    border-color: rgba(var(--v-theme-secondary), 0.55);
    box-shadow: 0 18px 36px -18px rgba(var(--v-theme-primary), 0.4);
    outline: none;
}

.home-shortcut__icon {
    flex-shrink: 0;
    display: grid;
    place-items: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 12px;
    background: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-secondary));
    font-size: 1.1rem;
}

.home-shortcut__body {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}

.home-shortcut__label {
    font-weight: 700;
    color: rgb(var(--v-theme-primary));
    letter-spacing: 0.01em;
}

.home-shortcut__desc {
    font-size: 0.875rem;
    line-height: 1.35;
    color: #5a5a6c;
}

.home-shortcut__chevron {
    margin-left: auto;
    font-size: 0.75rem;
    color: rgb(var(--v-theme-secondary));
    opacity: 0.85;
}

@media (max-width: 900px) {
    .home-shortcuts {
        grid-template-columns: 1fr;
    }
}
</style>

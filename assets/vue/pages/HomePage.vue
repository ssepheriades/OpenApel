<script setup lang="ts">
import { computed } from 'vue';
import HomeShortcuts from '@/components/ui/HomeShortcuts.vue';
import MarkdownContent from '@/components/ui/MarkdownContent.vue';
import { useAppStore } from '@/stores/app';

const appStore = useAppStore();

const hasIntro = computed(() => Boolean(appStore.homeText));
</script>

<template>
    <div class="home-page">
        <header class="home-hero" :class="{ 'home-hero--standalone': !hasIntro }">
            <div class="home-hero__wash" aria-hidden="true"></div>
            <v-container class="home-hero__inner">
                <div class="home-hero__content">
                    <p v-if="appStore.baseline" class="home-kicker">{{ appStore.baseline }}</p>
                    <h1 class="home-title">{{ appStore.homeTitle }}</h1>
                    <span class="home-rule" aria-hidden="true"></span>
                </div>
            </v-container>
        </header>

        <v-container v-if="hasIntro" class="home-letter-wrap">
            <article class="home-letter">
                <MarkdownContent :source="appStore.homeText ?? ''" />
            </article>
        </v-container>

        <v-container class="home-shortcuts-wrap" :class="{ 'home-shortcuts-wrap--flush': hasIntro }">
            <HomeShortcuts />
        </v-container>
    </div>
</template>

<style scoped>
.home-page {
    min-height: 100%;
    background:
        radial-gradient(1200px 400px at 80% -10%, rgba(var(--v-theme-secondary), 0.12), transparent 60%),
        linear-gradient(to bottom, #f3f4f8 0%, #ffffff 55%);
}

.home-hero {
    position: relative;
    overflow: hidden;
    color: #fff;
    padding: 5.5rem 0 7.5rem;
}

.home-hero--standalone {
    padding-bottom: 5.5rem;
}

.home-hero__wash {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(118deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-primary)) 42%, rgb(var(--v-theme-secondary)) 100%);
}

.home-hero__wash::before,
.home-hero__wash::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.home-hero__wash::before {
    width: 28rem;
    height: 28rem;
    top: -8rem;
    right: -6rem;
}

.home-hero__wash::after {
    width: 18rem;
    height: 18rem;
    bottom: -7rem;
    left: -4rem;
    background: rgba(255, 255, 255, 0.05);
}

.home-hero__inner {
    position: relative;
    z-index: 1;
}

.home-hero__content {
    max-width: 42rem;
    animation: home-in 0.7s ease-out;
}

.home-kicker {
    margin: 0 0 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: rgb(var(--v-theme-secondary));
}

.home-title {
    margin: 0;
    font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
    font-size: clamp(2.4rem, 6vw, 4.25rem);
    font-weight: 700;
    line-height: 1.12;
    letter-spacing: -0.02em;
    text-wrap: balance;
}

.home-rule {
    display: block;
    width: 4.5rem;
    height: 3px;
    margin-top: 1.6rem;
    background: rgb(var(--v-theme-secondary));
}

.home-letter-wrap {
    position: relative;
    z-index: 2;
    margin-top: -4.5rem;
    padding-bottom: 1.5rem;
}

.home-shortcuts-wrap {
    position: relative;
    z-index: 2;
    padding-top: 2.5rem;
    padding-bottom: 4.5rem;
}

.home-shortcuts-wrap--flush {
    padding-top: 0.5rem;
}

.home-letter {
    max-width: 42rem;
    padding: 2.5rem 2.25rem;
    background: #fff;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid rgba(var(--v-theme-primary), 0.08);
    border-left: 4px solid rgb(var(--v-theme-secondary));
    box-shadow: 0 18px 50px -24px rgba(var(--v-theme-primary), 0.45);
    animation: home-in 0.7s ease-out 0.12s both;
}

@keyframes home-in {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 600px) {
    .home-hero {
        padding: 3.5rem 0 5.5rem;
    }

    .home-hero--standalone {
        padding-bottom: 3.5rem;
    }

    .home-letter-wrap {
        margin-top: -3rem;
        padding-bottom: 1rem;
    }

    .home-shortcuts-wrap {
        padding-top: 1.5rem;
        padding-bottom: 3rem;
    }

    .home-shortcuts-wrap--flush {
        padding-top: 0.25rem;
    }

    .home-letter {
        padding: 1.6rem 1.25rem;
    }
}
</style>

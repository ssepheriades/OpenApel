<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useAppStore } from '@/stores/app';

interface SocialLink {
    label: string;
    href: string;
    icon: string;
}

const appStore = useAppStore();

const year = new Date().getFullYear();
const contactEmail = computed(() => appStore.settings?.contactEmail ?? null);
const legalPages = computed(() => appStore.documentPages);

const socialLinks = computed<SocialLink[]>(() => {
    const links: SocialLink[] = [];
    const facebookUrl = appStore.settings?.facebookUrl;
    const instagramUrl = appStore.settings?.instagramUrl;

    if (facebookUrl) {
        links.push({ label: 'Facebook', href: facebookUrl, icon: 'facebook' });
    }
    if (instagramUrl) {
        links.push({ label: 'Instagram', href: instagramUrl, icon: 'instagram' });
    }

    return links;
});
</script>

<template>
    <v-footer app class="d-flex flex-wrap justify-center align-center ga-4">
        <span class="text-caption">&copy; {{ year }} {{ appStore.siteName }}</span>

        <router-link
            v-for="page in legalPages"
            :key="page.slug"
            :to="{ name: page.slug }"
            class="text-caption footer-link"
        >
            {{ page.title }}
        </router-link>

        <a v-if="contactEmail" :href="`mailto:${contactEmail}`" class="text-caption footer-link">
            <FontAwesomeIcon :icon="['fas', 'envelope']" class="mr-1" />
            {{ contactEmail }}
        </a>

        <v-btn
            v-for="link in socialLinks"
            :key="link.label"
            :href="link.href"
            :aria-label="link.label"
            target="_blank"
            rel="noopener"
            icon
            size="small"
            variant="text"
        >
            <FontAwesomeIcon :icon="['fab', link.icon]" />
        </v-btn>
    </v-footer>
</template>

<style scoped>
.footer-link {
    color: inherit;
    text-decoration: none;
}
</style>

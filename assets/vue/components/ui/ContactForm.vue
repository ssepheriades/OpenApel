<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fetchSchoolClasses, type SchoolClass } from '@/api/audience';
import { submitContactMessage } from '@/api/contact';
import { ApiError } from '@/api/client';

const form = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null);
const name = ref('');
const email = ref('');
const phone = ref('');
const subject = ref('');
const message = ref('');
const schoolClassId = ref<number | null>(null);
const website = ref('');
const schoolClasses = ref<SchoolClass[]>([]);
const classesLoading = ref(false);
const isSubmitting = ref(false);
const error = ref<string | null>(null);
const submitted = ref(false);

const requiredRule = (label: string) => (value: string) => {
    return Boolean(value?.trim()) || `${label} est requis.`;
};

const emailRule = (value: string) => Boolean(value?.trim())
    ? /.+@.+\..+/.test(value) || 'Email invalide.'
    : "L'email est requis.";


const maxLengthRule = (max: number) => (value: string) => {
    return !value || value.length <= max || `${max} caractères maximum.`;
};

onMounted(async () => {
    classesLoading.value = true;
    try {
        schoolClasses.value = await fetchSchoolClasses();
    } catch {
        schoolClasses.value = [];
    } finally {
        classesLoading.value = false;
    }
});

async function onSubmit(): Promise<void> {
    const result = await form.value?.validate();
    if (!result?.valid) {
        return;
    }

    isSubmitting.value = true;
    error.value = null;

    try {
        await submitContactMessage({
            name: name.value.trim(),
            email: email.value.trim(),
            phone: phone.value.trim() === '' ? null : phone.value.trim(),
            subject: subject.value.trim(),
            message: message.value.trim(),
            schoolClassId: schoolClassId.value,
            website: website.value,
        });
        submitted.value = true;
    } catch (caught) {
        if (caught instanceof ApiError && caught.status === 422) {
            error.value = 'Vérifiez les champs saisis.';
        } else {
            error.value = "Impossible d'envoyer le message pour le moment.";
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <v-card v-if="submitted" class="contact-form" variant="outlined">
        <v-card-text class="text-center pa-8">
            <FontAwesomeIcon :icon="['fas', 'paper-plane']" class="contact-form__success-icon mb-4" />
            <p class="text-h6 text-primary font-weight-bold">Message envoyé</p>
            <p class="text-body-1 text-medium-emphasis mb-0">
                Merci, nous avons bien reçu votre message et vous répondrons dès que possible.
            </p>
        </v-card-text>
    </v-card>

    <v-card v-else class="contact-form" variant="outlined">
        <v-card-text class="pa-6 pa-sm-8">
            <v-alert v-if="error" type="error" variant="tonal" :text="error" class="mb-6" closable></v-alert>

            <v-form ref="form" @submit.prevent="onSubmit">
                <input v-model="website" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="contact-form__honeypot" />

                <v-text-field
                    v-model="name"
                    label="Nom et prénom"
                    variant="outlined"
                    :rules="[requiredRule('Le nom'), maxLengthRule(180)]"
                    class="mb-2"
                >
                    <template #prepend-inner>
                        <FontAwesomeIcon :icon="['fas', 'user']" class="text-medium-emphasis" />
                    </template>
                </v-text-field>

                <v-text-field
                    v-model="email"
                    label="Email"
                    type="email"
                    variant="outlined"
                    :rules="[emailRule, maxLengthRule(180)]"
                    class="mb-2"
                >
                    <template #prepend-inner>
                        <FontAwesomeIcon :icon="['fas', 'envelope']" class="text-medium-emphasis" />
                    </template>
                </v-text-field>

                <v-text-field
                    v-model="phone"
                    label="Téléphone (optionnel)"
                    type="tel"
                    variant="outlined"
                    :rules="[maxLengthRule(40)]"
                    class="mb-2"
                >
                    <template #prepend-inner>
                        <FontAwesomeIcon :icon="['fas', 'phone']" class="text-medium-emphasis" />
                    </template>
                </v-text-field>

                <v-text-field
                    v-model="subject"
                    label="Sujet"
                    variant="outlined"
                    :rules="[requiredRule('Le sujet'), maxLengthRule(255)]"
                    class="mb-2"
                />

                <v-select
                    v-model="schoolClassId"
                    :items="schoolClasses"
                    item-title="name"
                    item-value="id"
                    label="Classe de l'enfant (optionnel)"
                    variant="outlined"
                    clearable
                    :loading="classesLoading"
                    class="mb-2"
                />

                <v-textarea
                    v-model="message"
                    label="Message"
                    variant="outlined"
                    rows="6"
                    auto-grow
                    :rules="[requiredRule('Le message'), maxLengthRule(5000)]"
                    class="mb-2"
                />

                <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    block
                    :loading="isSubmitting"
                    :disabled="isSubmitting"
                >
                    <FontAwesomeIcon :icon="['fas', 'paper-plane']" class="mr-2" />
                    Envoyer
                </v-btn>

                <p class="text-caption text-medium-emphasis mt-4 mb-0">
                    Les informations saisies sont utilisées uniquement pour traiter votre demande.
                    Elles sont conservées par l'association le temps du suivi, puis archivées ou
                    supprimées.
                </p>
            </v-form>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.contact-form { max-width: 640px; margin: 0 auto; border-radius: 16px !important; }
.contact-form__success-icon { font-size: 2.5rem; color: rgb(var(--v-theme-primary)); }
.contact-form__honeypot { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }
</style>

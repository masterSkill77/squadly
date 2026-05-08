<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ sportTemplates: Array });

const sportLabels = {
    football: '⚽ Football',
    basketball: '🏀 Basketball',
    handball: '🤾 Handball',
    natation: '🏊 Natation',
    rugby: '🏉 Rugby',
    volleyball: '🏐 Volleyball',
};

const form = useForm({
    name: '',
    sport_type: '',
    contact_email: '',
    city: '',
});

const step = ref(0);
const steps = ['Organisation', 'Récapitulatif'];

const canNext = computed(() =>
    form.name.trim().length > 0 &&
    form.sport_type !== '' &&
    form.contact_email.trim().length > 0
);

function next() {
    if (step.value < steps.length - 1) step.value++;
}

function prev() {
    if (step.value > 0) step.value--;
}

function submit() {
    form.post(route('onboarding.organizer.store'));
}
</script>

<template>
    <Head title="Configurer votre organisation" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
        <div class="w-full max-w-lg">
            <!-- Logo -->
            <div class="mb-8 text-center">
                <img src="/squadly-logo-light.svg" alt="Squadly" class="mx-auto h-10" />
            </div>

            <!-- Progress -->
            <div class="mb-8 flex items-center gap-2">
                <template v-for="(s, i) in steps" :key="i">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                            :class="i <= step ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'"
                        >
                            {{ i + 1 }}
                        </div>
                        <span class="hidden text-sm font-medium sm:inline" :class="i <= step ? 'text-gray-900' : 'text-gray-400'">{{ s }}</span>
                    </div>
                    <div v-if="i < steps.length - 1" class="h-px flex-1" :class="i < step ? 'bg-emerald-500' : 'bg-gray-200'" />
                </template>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">

                <!-- Step 0 : Organisation -->
                <div v-if="step === 0" class="space-y-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Votre organisation</h2>
                        <p class="mt-1 text-sm text-gray-500">Renseignez les informations de base de votre ligue ou fédération.</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'organisation</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ex : Ligue Régionale Football"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="sport_type" class="block text-sm font-medium text-gray-700">Sport</label>
                        <select
                            id="sport_type"
                            v-model="form.sport_type"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choisir un sport</option>
                            <option v-for="s in sportTemplates" :key="s" :value="s">
                                {{ sportLabels[s] || s }}
                            </option>
                        </select>
                        <p v-if="form.errors.sport_type" class="mt-1 text-xs text-red-600">{{ form.errors.sport_type }}</p>
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Email de contact</label>
                        <input
                            id="contact_email"
                            v-model="form.contact_email"
                            type="email"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="contact@ligue.fr"
                        />
                        <p v-if="form.errors.contact_email" class="mt-1 text-xs text-red-600">{{ form.errors.contact_email }}</p>
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">Ville <span class="text-gray-400">(optionnel)</span></label>
                        <input
                            id="city"
                            v-model="form.city"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ex : Lyon"
                        />
                    </div>
                </div>

                <!-- Step 1 : Récapitulatif -->
                <div v-else class="space-y-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Récapitulatif</h2>
                        <p class="mt-1 text-sm text-gray-500">Vérifiez avant de créer votre organisation.</p>
                    </div>

                    <div class="space-y-4 rounded-xl border border-gray-100 bg-gray-50 p-5">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Organisation</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ form.name }}
                                <span v-if="form.city" class="font-normal text-gray-500"> — {{ form.city }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Sport</p>
                            <p class="text-sm text-gray-900">{{ sportLabels[form.sport_type] || form.sport_type }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Email de contact</p>
                            <p class="text-sm text-gray-900">{{ form.contact_email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Errors globaux -->
                <div v-if="Object.keys(form.errors).length && step === 1" class="mt-4 rounded-lg bg-red-50 p-3">
                    <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-red-600">{{ msg }}</p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-between">
                    <button
                        v-if="step > 0"
                        type="button"
                        class="text-sm text-gray-500 transition hover:text-gray-700"
                        @click="prev"
                    >
                        Précédent
                    </button>
                    <span v-else />

                    <button
                        v-if="step === 0"
                        type="button"
                        :disabled="!canNext"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                        @click="next"
                    >
                        Suivant
                    </button>
                    <button
                        v-else
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        @click="submit"
                    >
                        Créer mon organisation
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

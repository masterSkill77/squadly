<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: '',
    sport_type: '',
    contact_email: props.user.email,
    city: '',
});

const sports = [
    { value: 'football', label: 'Football' },
    { value: 'basketball', label: 'Basketball' },
    { value: 'handball', label: 'Handball' },
    { value: 'natation', label: 'Natation' },
    { value: 'rugby', label: 'Rugby' },
    { value: 'volleyball', label: 'Volleyball' },
    { value: 'autre', label: 'Autre' },
];

function submit() {
    form.post(route('organizer.setup'));
}
</script>

<template>
    <Head title="Créer mon organisation" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-teal-50 px-4">
        <div class="w-full max-w-lg">
            <div class="mb-8 text-center">
                <img src="/squadly-logo-light.svg" alt="Squadly" class="mx-auto h-10" />
                <h1 class="mt-6 text-2xl font-bold text-gray-900">Bienvenue, {{ user.name }} !</h1>
                <p class="mt-2 text-gray-500">Créez votre organisation pour commencer à gérer vos compétitions.</p>
            </div>

            <form class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm" @submit.prevent="submit">
                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'organisation</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            placeholder="Ex : Ligue Régionale Football"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <label for="sport_type" class="block text-sm font-medium text-gray-700">Sport principal</label>
                        <select
                            id="sport_type"
                            v-model="form.sport_type"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choisir un sport</option>
                            <option v-for="sport in sports" :key="sport.value" :value="sport.value">{{ sport.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.sport_type" />
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Email de contact</label>
                        <input
                            id="contact_email"
                            v-model="form.contact_email"
                            type="email"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.contact_email" />
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">Ville <span class="text-gray-400">(optionnel)</span></label>
                        <input
                            id="city"
                            v-model="form.city"
                            type="text"
                            placeholder="Ex : Paris"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.city" />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-8 w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    Créer mon organisation
                </button>
            </form>
        </div>
    </div>
</template>

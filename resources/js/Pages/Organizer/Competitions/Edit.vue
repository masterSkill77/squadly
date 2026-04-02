<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    competition: Object,
});

const form = useForm({
    name: props.competition.name,
    sport_type: props.competition.sport_type,
    season: props.competition.season,
    format: props.competition.format,
    status: props.competition.status,
    description: props.competition.description || '',
    rules: props.competition.rules || {},
    starts_at: props.competition.starts_at?.substring(0, 10) || '',
    ends_at: props.competition.ends_at?.substring(0, 10) || '',
});

const formats = [
    { value: 'league', label: 'Championnat' },
    { value: 'cup', label: 'Coupe' },
    { value: 'group_knockout', label: 'Poules + Élimination' },
    { value: 'custom', label: 'Personnalisé' },
];

const statuses = [
    { value: 'draft', label: 'Brouillon' },
    { value: 'open', label: 'Inscriptions ouvertes' },
    { value: 'ongoing', label: 'En cours' },
    { value: 'finished', label: 'Terminée' },
];

const sports = [
    { value: 'football', label: 'Football' },
    { value: 'basketball', label: 'Basketball' },
    { value: 'handball', label: 'Handball' },
    { value: 'natation', label: 'Natation' },
    { value: 'rugby', label: 'Rugby' },
    { value: 'volleyball', label: 'Volleyball' },
];

function submit() {
    form.put(route('organizer.competitions.update', props.competition.id));
}
</script>

<template>
    <Head :title="`Modifier — ${competition.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('organizer.competitions.show', competition.id)"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Modifier la compétition</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Informations générales</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sport</label>
                        <select v-model="form.sport_type" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option v-for="s in sports" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.sport_type" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Saison</label>
                        <input v-model="form.season" type="text" required placeholder="2025-2026" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.season" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Format</label>
                        <select v-model="form.format" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option v-for="f in formats" :key="f.value" :value="f.value">{{ f.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.format" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select v-model="form.status" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.status" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                        <input v-model="form.starts_at" type="date" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.starts_at" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input v-model="form.ends_at" type="date" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.ends_at" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.description" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <Link
                    :href="route('organizer.competitions.show', competition.id)"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Annuler
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    Enregistrer
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

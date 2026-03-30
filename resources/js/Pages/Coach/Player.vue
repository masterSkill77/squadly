<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { sportLabels } from '@/Utils/sportLabels';

const props = defineProps({ team: Object, player: Object });

const infoForm = useForm({
    first_name: props.player.first_name,
    last_name: props.player.last_name,
    phone: props.player.phone || '',
    birth_date: props.player.birth_date || '',
});

const sportForm = useForm({
    sport_profile: { ...(props.player.sport_profile || {}) },
});

function submitInfo() {
    infoForm.put(route('coach.team.player.update', [props.team.id, props.player.user_id]));
}

function submitSport() {
    sportForm.put(route('coach.team.player.sport-profile', [props.team.id, props.player.user_id]));
}
</script>

<template>
    <Head :title="`${player.first_name} ${player.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('coach.team', team.id)" class="text-gray-400 transition hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </Link>
                <h2 class="text-lg font-semibold text-gray-900">{{ player.first_name }} {{ player.last_name }}</h2>
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">{{ team.name }}</span>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <!-- Player info -->
                <div class="rounded-xl border border-gray-100 bg-white p-6">
                    <h3 class="text-base font-semibold text-gray-900">Informations</h3>
                    <form class="mt-4 space-y-4" @submit.prevent="submitInfo">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input v-model="infoForm.first_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <InputError class="mt-1" :message="infoForm.errors.first_name" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input v-model="infoForm.last_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <InputError class="mt-1" :message="infoForm.errors.last_name" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input v-model="infoForm.phone" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                                <input v-model="infoForm.birth_date" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input :value="player.email" type="email" disabled class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500" />
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="infoForm.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Enregistrer</button>
                        </div>
                    </form>
                </div>

                <!-- Sport profile -->
                <div class="rounded-xl border border-gray-100 bg-white p-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ sportLabels[team.sport_type] || team.sport_type }} — Profil sportif
                    </h3>

                    <form v-if="team.sport_template?.fields?.length" class="mt-4 space-y-4" @submit.prevent="submitSport">
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="field in team.sport_template.fields" :key="field.key">
                                <label class="block text-xs font-medium text-gray-600">{{ field.label }}</label>
                                <select
                                    v-if="field.type === 'select'"
                                    v-model="sportForm.sport_profile[field.key]"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                                    <option value="">—</option>
                                    <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                                <input
                                    v-else
                                    v-model="sportForm.sport_profile[field.key]"
                                    :type="field.type || 'text'"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="sportForm.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Enregistrer</button>
                        </div>
                    </form>

                    <p v-else class="mt-3 text-sm text-gray-400">Aucun champ défini pour ce sport.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Player card -->
                <div class="rounded-xl border border-gray-100 bg-white p-6 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
                        {{ player.first_name.charAt(0) }}{{ player.last_name.charAt(0) }}
                    </div>
                    <p class="mt-3 text-sm font-semibold text-gray-900">{{ player.first_name }} {{ player.last_name }}</p>
                    <p class="text-xs text-gray-500">{{ player.email }}</p>

                    <!-- Quick sport info summary -->
                    <div v-if="player.sport_profile && team.sport_template?.fields?.length" class="mt-4 space-y-1 text-left">
                        <div v-for="field in team.sport_template.fields" :key="field.key" class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">{{ field.label }}</span>
                            <span class="font-medium text-gray-700">{{ player.sport_profile[field.key] || '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Team info -->
                <div class="rounded-xl border border-blue-100 bg-blue-50/30 p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        <p class="text-sm font-medium text-blue-700">{{ team.name }}</p>
                    </div>
                    <p class="mt-1 text-xs text-blue-600/70">{{ sportLabels[team.sport_type] || team.sport_type }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

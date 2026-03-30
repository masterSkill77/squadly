<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddPlayerModal from '@/Components/Coach/AddPlayerModal.vue';
import { sportLabels } from '@/Utils/sportLabels';

const props = defineProps({ team: Object, players: Array });

const showAddPlayer = ref(false);
</script>

<template>
    <Head :title="team.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('coach.effectifs')" class="text-gray-400 transition hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </Link>
                    <h2 class="text-lg font-semibold text-gray-900">{{ team.name }}</h2>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ sportLabels[team.sport_type] || team.sport_type }}</span>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Team info -->
            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-gray-100 bg-white p-5">
                <div>
                    <p class="text-xs text-gray-400">Catégorie</p>
                    <p class="text-sm font-medium text-gray-900">{{ team.age_category || '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Saison</p>
                    <p class="text-sm font-medium text-gray-900">{{ team.season || '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Effectif</p>
                    <p class="text-sm font-medium text-gray-900">{{ players.length }} joueur{{ players.length > 1 ? 's' : '' }}</p>
                </div>
            </div>

            <!-- Players -->
            <div>
                <div class="w-full flex align-center justify-between mb-4">
                    <h3 class="mb-3 text-base font-semibold text-gray-900">Effectif</h3>
                    <button
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="showAddPlayer = true"
                    >
                        Ajouter un joueur
                    </button>
                </div>

                <div v-if="players.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-50 bg-gray-50/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-400">Joueur</th>
                                <th
                                    v-for="field in (team.sport_template?.fields || [])"
                                    :key="field.key"
                                    class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-400 sm:table-cell"
                                >
                                    {{ field.label }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-400">Contact</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="p in players" :key="p.user_id" class="transition hover:bg-gray-50/50">
                                <td class="px-4 py-3">
                                    <Link :href="route('coach.team.player', [team.id, p.user_id])" class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                                            {{ p.first_name.charAt(0) }}{{ p.last_name.charAt(0) }}
                                        </div>
                                        <span class="font-medium text-gray-900 hover:text-emerald-600 transition">{{ p.first_name }} {{ p.last_name }}</span>
                                    </Link>
                                </td>
                                <td
                                    v-for="field in (team.sport_template?.fields || [])"
                                    :key="field.key"
                                    class="hidden px-4 py-3 text-gray-600 sm:table-cell"
                                >
                                    {{ p.sport_profile?.[field.key] || '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    <span v-if="p.phone" class="text-xs">{{ p.phone }}</span>
                                    <span v-else class="text-xs text-gray-300">{{ p.email }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun joueur dans cette équipe.</p>
                    <button
                        class="mt-3 text-sm font-medium text-emerald-600 transition hover:text-emerald-700"
                        @click="showAddPlayer = true"
                    >
                        Ajouter le premier joueur
                    </button>
                </div>
            </div>
        </div>

        <AddPlayerModal :show="showAddPlayer" :team-id="team.id" :sport-template="team.sport_template" @close="showAddPlayer = false" />
    </AuthenticatedLayout>
</template>

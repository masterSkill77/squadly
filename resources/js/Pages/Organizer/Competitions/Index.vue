<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    competitions: Array,
    organizer: Object,
});

const showDeleteModal = ref(false);
const competitionToDelete = ref(null);

const sportLabels = {
    football: 'Football',
    basketball: 'Basketball',
    handball: 'Handball',
    volleyball: 'Volleyball',
    rugby: 'Rugby',
    natation: 'Natation',
};

const formatLabels = {
    league: 'Championnat',
    cup: 'Coupe',
    group_knockout: 'Poules + Élimination',
    league_playoffs: 'Championnat + Playoffs',
    custom: 'Personnalisé',
};

const confirmDelete = (competition) => {
    competitionToDelete.value = competition;
    showDeleteModal.value = true;
};

const deleteCompetition = () => {
    if (!competitionToDelete.value) return;
    router.delete(route('organizer.competitions.destroy', competitionToDelete.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            competitionToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Compétitions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Compétitions</h2>
        </template>

        <div class="space-y-6">
            <!-- Toolbar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    {{ competitions.length }} compétition{{ competitions.length > 1 ? 's' : '' }}
                </p>
                <Link
                    :href="route('organizer.competitions.create')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouvelle compétition
                </Link>
            </div>

            <!-- Table -->
            <div v-if="competitions.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nom</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sport</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Saison</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Format</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Clubs</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="comp in competitions" :key="comp.id" class="transition hover:bg-gray-50/50">
                            <td class="px-5 py-3">
                                <Link
                                    :href="route('organizer.competitions.show', comp.id)"
                                    class="text-sm font-medium text-gray-900 hover:text-emerald-600"
                                >
                                    {{ comp.name }}
                                </Link>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ sportLabels[comp.sport_type] || comp.sport_type }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ comp.season }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ formatLabels[comp.format] || comp.format }}
                            </td>
                            <td class="px-5 py-3">
                                <CompetitionBadge :status="comp.status" />
                            </td>
                            <td class="px-5 py-3 text-center text-sm text-gray-600">
                                {{ comp.clubs_count ?? 0 }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('organizer.competitions.show', comp.id)"
                                        class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                        title="Voir"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('organizer.competitions.edit', comp.id)"
                                        class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                        title="Modifier"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                    </Link>
                                    <button
                                        class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                        title="Supprimer"
                                        @click="confirmDelete(comp)"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 10.875h-2.25A3.375 3.375 0 0 0 7.5 14.25v4.5" />
                </svg>
                <p class="mt-3 text-sm font-medium text-gray-600">Aucune competition</p>
                <p class="mt-1 text-xs text-gray-400">Creez votre premiere competition pour commencer.</p>
                <Link
                    :href="route('organizer.competitions.create')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                    Nouvelle competition
                </Link>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" max-width="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Supprimer la competition</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Etes-vous sur de vouloir supprimer
                    <strong>{{ competitionToDelete?.name }}</strong> ?
                    Cette action est irreversible.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Annuler
                    </button>
                    <button
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                        @click="deleteCompetition"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

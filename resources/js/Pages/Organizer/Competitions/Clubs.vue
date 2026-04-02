<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    competition: Object,
});

const statusLabels = {
    pending: 'En attente',
    approved: 'Approuvé',
    rejected: 'Refusé',
};

const statusClasses = {
    pending: 'bg-amber-50 text-amber-700',
    approved: 'bg-emerald-50 text-emerald-700',
    rejected: 'bg-red-50 text-red-700',
};

const updateClub = (ccId, data) => {
    router.put(route('organizer.competitions.clubs.update', [props.competition.id, ccId]), data, {
        preserveScroll: true,
    });
};

const approveClub = (ccId) => updateClub(ccId, { status: 'approved' });
const rejectClub = (ccId) => updateClub(ccId, { status: 'rejected' });
const assignPhase = (ccId, phaseId) => updateClub(ccId, { phase_id: phaseId || null });

const removeClub = (ccId) => {
    if (!confirm('Retirer ce club de la compétition ?')) return;
    router.delete(route('organizer.competitions.clubs.destroy', [props.competition.id, ccId]), {
        preserveScroll: true,
    });
};

const hasPending = () => (props.competition.competition_clubs ?? []).some(c => c.status === 'pending');

const approveAll = () => {
    if (!confirm('Approuver tous les clubs en attente ?')) return;
    router.post(route('organizer.competitions.clubs.approve-all', props.competition.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Clubs — ${competition.name}`" />

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
                    <h2 class="text-lg font-semibold text-gray-900">Clubs inscrits</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>{{ competition.competition_clubs?.filter(c => c.status === 'approved').length || 0 }} approuvé(s)</span>
                <span class="text-gray-300">|</span>
                <span>{{ competition.competition_clubs?.filter(c => c.status === 'pending').length || 0 }} en attente</span>
                <span class="text-gray-300">|</span>
                <span>{{ competition.competition_clubs?.length || 0 }} total</span>
            </div>

            <!-- Info + Approve all -->
            <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 p-4">
                <p class="text-sm text-blue-700">Les clubs s'inscrivent eux-mêmes depuis leur espace. Vous pouvez approuver ou refuser les demandes ici.</p>
                <button
                    v-if="hasPending()"
                    class="ml-4 shrink-0 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="approveAll"
                >
                    Tout approuver
                </button>
            </div>

            <!-- Table -->
            <div v-if="competition.competition_clubs?.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Club</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phase</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="cc in competition.competition_clubs"
                            :key="cc.id"
                            class="transition hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500">
                                        {{ (cc.club?.name || '?').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ cc.club?.name || '—' }}</p>
                                        <p v-if="cc.club?.city" class="text-xs text-gray-500">{{ cc.club.city }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusClasses[cc.status] || 'bg-gray-100 text-gray-600'"
                                >
                                    {{ statusLabels[cc.status] || cc.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <select
                                    class="rounded-lg border border-gray-200 px-2 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500"
                                    :value="cc.phase_id || ''"
                                    @change="assignPhase(cc.id, $event.target.value)"
                                >
                                    <option value="">Non assigné</option>
                                    <option
                                        v-for="phase in competition.phases"
                                        :key="phase.id"
                                        :value="phase.id"
                                    >
                                        {{ phase.name }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <template v-if="cc.status === 'pending'">
                                        <button
                                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                                            @click="approveClub(cc.id)"
                                        >
                                            Approuver
                                        </button>
                                        <button
                                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50"
                                            @click="rejectClub(cc.id)"
                                        >
                                            Refuser
                                        </button>
                                    </template>
                                    <button
                                        class="rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-400 transition hover:bg-gray-100 hover:text-red-600"
                                        @click="removeClub(cc.id)"
                                    >
                                        Retirer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-dashed border-gray-300 p-12 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                <p class="mt-3 text-sm text-gray-500">Aucun club inscrit à cette compétition.</p>
                <p class="mt-1 text-xs text-gray-400">Les clubs peuvent s'inscrire depuis leur espace Compétitions.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

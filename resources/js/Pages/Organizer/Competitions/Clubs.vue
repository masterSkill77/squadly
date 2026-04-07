<script setup>
import { ref, computed } from 'vue';
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

// ─── Selection ───
const selected = ref([]);
const bulkPhaseId = ref('');

const allClubs = computed(() => props.competition.competition_clubs ?? []);
const allSelected = computed(() => selected.value.length === allClubs.value.length && allClubs.value.length > 0);
const someSelected = computed(() => selected.value.length > 0 && !allSelected.value);

const toggleAll = () => {
    if (allSelected.value) {
        selected.value = [];
    } else {
        selected.value = allClubs.value.map(cc => cc.id);
    }
};

const toggleOne = (id) => {
    const idx = selected.value.indexOf(id);
    if (idx >= 0) {
        selected.value.splice(idx, 1);
    } else {
        selected.value.push(id);
    }
};

const bulkAssignPhase = () => {
    if (!selected.value.length || !bulkPhaseId.value) return;
    const phaseName = props.competition.phases?.find(p => p.id == bulkPhaseId.value)?.name || '';
    if (!confirm(`Assigner ${selected.value.length} club(s) à "${phaseName}" ?`)) return;

    router.post(route('organizer.competitions.clubs.bulk-assign', props.competition.id), {
        club_ids: selected.value,
        phase_id: bulkPhaseId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = [];
            bulkPhaseId.value = '';
        },
    });
};

// ─── Individual actions ───
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

const hasPending = () => allClubs.value.some(c => c.status === 'pending');

const approveAll = () => {
    if (!confirm('Approuver tous les clubs en attente ?')) return;
    router.post(route('organizer.competitions.clubs.approve-all', props.competition.id), {}, {
        preserveScroll: true,
    });
};

const registerAll = () => {
    if (!confirm('Inscrire tous les clubs existants dans cette compétition ? (mode test)')) return;
    router.post(route('organizer.competitions.clubs.register-all', props.competition.id), {}, {
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
                <span>{{ allClubs.filter(c => c.status === 'approved').length }} approuvé(s)</span>
                <span class="text-gray-300">|</span>
                <span>{{ allClubs.filter(c => c.status === 'pending').length }} en attente</span>
                <span class="text-gray-300">|</span>
                <span>{{ allClubs.length }} total</span>
            </div>

            <!-- Info + Approve all -->
            <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 p-4">
                <p class="text-sm text-blue-700">Les clubs s'inscrivent eux-mêmes depuis leur espace. Vous pouvez approuver ou refuser les demandes ici.</p>
                <div class="ml-4 flex shrink-0 gap-2">
                    <button
                        v-if="hasPending()"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="approveAll"
                    >
                        Tout approuver
                    </button>
                </div>
            </div>

            <!-- DEV: Register all clubs -->
            <div class="flex items-center justify-between rounded-lg border border-dashed border-amber-300 bg-amber-50 p-4">
                <div>
                    <p class="text-sm font-medium text-amber-700">Mode test</p>
                    <p class="text-xs text-amber-600">Inscrire automatiquement tous les clubs existants dans cette compétition.</p>
                </div>
                <button
                    class="ml-4 shrink-0 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
                    @click="registerAll"
                >
                    Inscrire tous les clubs
                </button>
            </div>

            <!-- Bulk actions bar -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="selected.length > 0"
                    class="flex items-center gap-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 shadow-sm"
                >
                    <span class="text-sm font-semibold text-emerald-700">
                        {{ selected.length }} club(s) sélectionné(s)
                    </span>

                    <div class="flex items-center gap-2">
                        <select
                            v-model="bulkPhaseId"
                            class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choisir une poule...</option>
                            <option
                                v-for="phase in competition.phases"
                                :key="phase.id"
                                :value="phase.id"
                            >
                                {{ phase.name }}
                            </option>
                        </select>
                        <button
                            class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="!bulkPhaseId"
                            @click="bulkAssignPhase"
                        >
                            Assigner
                        </button>
                    </div>

                    <button
                        class="ml-auto text-xs text-gray-500 transition hover:text-gray-700"
                        @click="selected = []"
                    >
                        Tout désélectionner
                    </button>
                </div>
            </Transition>

            <!-- Table -->
            <div v-if="allClubs.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="w-10 px-3 py-3">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    :indeterminate="someSelected"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    @change="toggleAll"
                                />
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Club</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phase</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="cc in allClubs"
                            :key="cc.id"
                            class="transition"
                            :class="selected.includes(cc.id) ? 'bg-emerald-50/50' : 'hover:bg-gray-50/50'"
                        >
                            <td class="w-10 px-3 py-3">
                                <input
                                    type="checkbox"
                                    :checked="selected.includes(cc.id)"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                    @change="toggleOne(cc.id)"
                                />
                            </td>
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

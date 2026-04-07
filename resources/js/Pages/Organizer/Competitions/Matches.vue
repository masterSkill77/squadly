<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';
import CalendarGrid from '@/Components/CalendarGrid.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    competition: Object,
});

const showAddModal = ref(false);
const filterPhaseId = ref(null);

const addForm = useForm({
    phase_id: '',
    home_club_id: '',
    away_club_id: '',
    scheduled_at: '',
    location: '',
});

const approvedClubs = (props.competition.competition_clubs || []).filter(cc => cc.status === 'approved');

const allGames = computed(() => {
    const games = [];
    for (const phase of props.competition.phases || []) {
        for (const game of phase.games || []) {
            games.push({ ...game, phase_name: phase.name, phase_status: phase.status });
        }
    }
    return games;
});

const filteredGames = computed(() => {
    if (!filterPhaseId.value) return allGames.value;
    return allGames.value.filter(g => g.phase_id === filterPhaseId.value);
});

const addMatch = () => {
    addForm.post(route('organizer.competitions.matches.store', props.competition.id), {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset();
            showAddModal.value = false;
        },
    });
};

const statusLabels = {
    scheduled: 'Programmé',
    ongoing: 'En cours',
    finished: 'Terminé',
    cancelled: 'Annulé',
};

const statusClasses = {
    scheduled: 'bg-blue-50 text-blue-700',
    ongoing: 'bg-amber-50 text-amber-700',
    finished: 'bg-gray-100 text-gray-600',
    cancelled: 'bg-red-50 text-red-600',
};

function updateStatus(gameId, status) {
    router.put(route('organizer.matches.update', gameId), { status }, { preserveScroll: true });
}

function deleteGame(gameId) {
    if (!confirm('Supprimer ce match ?')) return;
    router.delete(route('organizer.matches.destroy', gameId), { preserveScroll: true });
}

const scheduledCount = () => {
    return allGames.value.filter(g => g.status === 'scheduled' && g.home_club_id && g.away_club_id).length;
};

function simulateScores() {
    const count = scheduledCount();
    if (!confirm(`Simuler les scores de ${count} match(s) programmé(s) ? (mode test)`)) return;
    router.post(route('organizer.competitions.simulate-scores', props.competition.id), {}, {
        preserveScroll: true,
    });
}

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const homeLost = (game) => game.status === 'finished' && game.home_score < game.away_score;
const awayLost = (game) => game.status === 'finished' && game.away_score < game.home_score;
</script>

<template>
    <Head :title="`Matchs — ${competition.name}`" />

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
                    <h2 class="text-lg font-semibold text-gray-900">Matchs</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- DEV: Simulate scores -->
            <div v-if="scheduledCount() > 0" class="flex items-center justify-between rounded-lg border border-dashed border-amber-300 bg-amber-50 p-4">
                <div>
                    <p class="text-sm font-medium text-amber-700">Mode test</p>
                    <p class="text-xs text-amber-600">
                        Simuler automatiquement les scores de {{ scheduledCount() }} match(s) programmé(s).
                        Les scores seront adaptés au sport ({{ competition.sport_type }}).
                    </p>
                </div>
                <button
                    class="ml-4 shrink-0 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
                    @click="simulateScores"
                >
                    Simuler les scores
                </button>
            </div>

            <!-- Calendar grid with matches -->
            <CalendarGrid :items="filteredGames" date-key="scheduled_at">
                <!-- Phase filter -->
                <template #filters>
                    <button
                        class="rounded-full border-2 px-3 py-1 text-xs font-medium transition"
                        :class="!filterPhaseId ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                        @click="filterPhaseId = null"
                    >
                        Toutes les phases
                    </button>
                    <button
                        v-for="phase in competition.phases"
                        :key="phase.id"
                        class="rounded-full border-2 px-3 py-1 text-xs font-medium transition"
                        :class="filterPhaseId === phase.id ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                        @click="filterPhaseId = phase.id"
                    >
                        {{ phase.name }}
                    </button>
                </template>

                <!-- Action button -->
                <template #actions>
                    <button
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="showAddModal = true"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter un match
                    </button>
                </template>

                <!-- Calendar day item (sidebar) -->
                <template #day-item="{ item: game }">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[10px] font-medium text-gray-400">{{ game.phase_name }}</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="statusClasses[game.status] || 'bg-gray-100 text-gray-600'"
                            >
                                {{ statusLabels[game.status] || game.status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1 text-right">
                                <p class="truncate text-sm font-semibold" :class="homeLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.home_club?.name || '—' }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-lg bg-gray-100 px-2.5 py-1">
                                <span v-if="game.home_score !== null && game.away_score !== null" class="text-sm font-bold text-gray-900">
                                    {{ game.home_score }} - {{ game.away_score }}
                                </span>
                                <span v-else class="text-xs font-medium text-gray-400">VS</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold" :class="awayLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.away_club?.name || '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                            <span>{{ formatTime(game.scheduled_at) }}</span>
                            <span v-if="game.location">{{ game.location }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-3 flex items-center justify-between border-t border-gray-50 pt-2">
                            <div class="flex items-center gap-1">
                                <button
                                    v-if="game.status === 'scheduled'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-amber-600 transition hover:bg-amber-50"
                                    @click="updateStatus(game.id, 'ongoing')"
                                >
                                    Démarrer
                                </button>
                                <button
                                    v-if="game.status === 'scheduled' || game.status === 'ongoing'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-red-500 transition hover:bg-red-50"
                                    @click="updateStatus(game.id, 'cancelled')"
                                >
                                    Annuler
                                </button>
                                <button
                                    v-if="game.status !== 'finished' && game.status !== 'cancelled'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-gray-400 transition hover:bg-gray-50 hover:text-red-600"
                                    @click="deleteGame(game.id)"
                                >
                                    Supprimer
                                </button>
                            </div>
                            <Link
                                v-if="game.status !== 'cancelled'"
                                :href="route('organizer.matches.score', game.id)"
                                class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                Score
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- List view item -->
                <template #list-item="{ item: game }">
                    <div class="flex items-center gap-2 rounded-xl border border-gray-100 bg-white transition hover:border-emerald-200 hover:shadow-sm">
                        <div class="flex flex-1 items-center gap-4 p-4">
                            <div class="shrink-0 text-center">
                                <p class="text-sm font-semibold text-gray-900">{{ formatTime(game.scheduled_at) }}</p>
                                <p class="text-[10px] text-gray-400">{{ game.phase_name }}</p>
                            </div>
                            <div class="h-10 w-px bg-gray-200" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold" :class="homeLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.home_club?.name || '—' }}</span>
                                    <span v-if="game.home_score !== null && game.away_score !== null" class="rounded bg-gray-900 px-1.5 py-0.5 text-xs font-bold text-white">
                                        {{ game.home_score }} - {{ game.away_score }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">vs</span>
                                    <span class="text-sm font-semibold" :class="awayLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.away_club?.name || '—' }}</span>
                                </div>
                                <p v-if="game.location" class="mt-0.5 truncate text-xs text-gray-500">{{ game.location }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="statusClasses[game.status] || 'bg-gray-100 text-gray-600'"
                            >
                                {{ statusLabels[game.status] || game.status }}
                            </span>
                        </div>
                        <div class="mr-3 flex shrink-0 gap-1.5">
                            <button
                                v-if="game.status === 'scheduled'"
                                class="rounded-lg border border-gray-200 p-2 text-amber-500 transition hover:border-amber-300 hover:bg-amber-50"
                                title="Démarrer"
                                @click="updateStatus(game.id, 'ongoing')"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                            </button>
                            <Link
                                v-if="game.status !== 'cancelled'"
                                :href="route('organizer.matches.score', game.id)"
                                class="rounded-lg border border-gray-200 p-2 text-emerald-500 transition hover:border-emerald-300 hover:bg-emerald-50"
                                title="Saisir le score"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- Card view item -->
                <template #card-item="{ item: game }">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ formatDateTime(game.scheduled_at) }}</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="statusClasses[game.status] || 'bg-gray-100 text-gray-600'"
                            >
                                {{ statusLabels[game.status] || game.status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1 text-right">
                                <p class="truncate text-sm font-semibold" :class="homeLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.home_club?.name || '—' }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-lg bg-gray-100 px-3 py-1.5">
                                <span v-if="game.home_score !== null && game.away_score !== null" class="text-sm font-bold text-gray-900">
                                    {{ game.home_score }} - {{ game.away_score }}
                                </span>
                                <span v-else class="text-xs font-medium text-gray-400">VS</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold" :class="awayLost(game) ? 'text-gray-300' : 'text-gray-900'">{{ game.away_club?.name || '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center justify-between">
                            <span v-if="game.location" class="text-xs text-gray-400">{{ game.location }}</span>
                            <span class="text-[10px] font-medium text-gray-400">{{ game.phase_name }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-3 flex items-center justify-between border-t border-gray-50 pt-3">
                            <div class="flex items-center gap-1">
                                <button
                                    v-if="game.status === 'scheduled'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-amber-600 transition hover:bg-amber-50"
                                    @click="updateStatus(game.id, 'ongoing')"
                                >
                                    Démarrer
                                </button>
                                <button
                                    v-if="game.status === 'scheduled' || game.status === 'ongoing'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-red-500 transition hover:bg-red-50"
                                    @click="updateStatus(game.id, 'cancelled')"
                                >
                                    Annuler
                                </button>
                                <button
                                    v-if="game.status !== 'finished' && game.status !== 'cancelled'"
                                    class="rounded-md px-2 py-1 text-xs font-medium text-gray-400 transition hover:bg-gray-50 hover:text-red-600"
                                    @click="deleteGame(game.id)"
                                >
                                    Supprimer
                                </button>
                            </div>
                            <Link
                                v-if="game.status !== 'cancelled'"
                                :href="route('organizer.matches.score', game.id)"
                                class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                Saisir le score
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- Empty state -->
                <template #empty>
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-600">Aucun match programmé</p>
                    <p class="mt-1 text-xs text-gray-400">Ajoutez un match ou générez le calendrier depuis les phases.</p>
                </template>

                <template #day-empty>
                    <p class="text-sm text-gray-400">Aucun match ce jour.</p>
                </template>
            </CalendarGrid>
        </div>

        <!-- Add match modal -->
        <Modal :show="showAddModal" max-width="lg" @close="showAddModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Ajouter un match</h3>
                <form class="space-y-4" @submit.prevent="addMatch">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Phase *</label>
                        <select
                            v-model="addForm.phase_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choisir une phase</option>
                            <option
                                v-for="phase in competition.phases"
                                :key="phase.id"
                                :value="phase.id"
                            >
                                {{ phase.name }}
                            </option>
                        </select>
                        <p v-if="addForm.errors.phase_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.phase_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Club domicile *</label>
                            <select
                                v-model="addForm.home_club_id"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Choisir un club</option>
                                <option
                                    v-for="cc in approvedClubs"
                                    :key="cc.club.id"
                                    :value="cc.club.id"
                                >
                                    {{ cc.club.name }}
                                </option>
                            </select>
                            <p v-if="addForm.errors.home_club_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.home_club_id }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Club extérieur *</label>
                            <select
                                v-model="addForm.away_club_id"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Choisir un club</option>
                                <option
                                    v-for="cc in approvedClubs"
                                    :key="cc.club.id"
                                    :value="cc.club.id"
                                >
                                    {{ cc.club.name }}
                                </option>
                            </select>
                            <p v-if="addForm.errors.away_club_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.away_club_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date et heure *</label>
                            <input
                                v-model="addForm.scheduled_at"
                                type="datetime-local"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="addForm.errors.scheduled_at" class="mt-1 text-xs text-red-600">{{ addForm.errors.scheduled_at }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Lieu</label>
                            <input
                                v-model="addForm.location"
                                type="text"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Stade, gymnase..."
                            />
                            <p v-if="addForm.errors.location" class="mt-1 text-xs text-red-600">{{ addForm.errors.location }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            @click="showAddModal = false"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="addForm.processing || !addForm.phase_id || !addForm.home_club_id || !addForm.away_club_id || !addForm.scheduled_at"
                        >
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

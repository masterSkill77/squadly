<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

const props = defineProps({
    game: Object,
});

const scoreForm = useForm({
    home_score: props.game.home_score ?? '',
    away_score: props.game.away_score ?? '',
    status: props.game.status || 'scheduled',
});

const statusOptions = [
    { value: 'scheduled', label: 'Programme' },
    { value: 'ongoing', label: 'En cours' },
    { value: 'finished', label: 'Termine' },
    { value: 'cancelled', label: 'Annule' },
];

const submitScore = () => {
    scoreForm.patch(route('organizer.matches.update-score', props.game.id), {
        preserveScroll: true,
    });
};

// Player stats management
const showStatsForm = ref(false);
const playerStatsForm = useForm({
    player_id: '',
    club_side: 'home',
    goals: 0,
    assists: 0,
    yellow_cards: 0,
    red_cards: 0,
    minutes_played: 0,
});

const homeStats = computed(() => {
    return (props.game.player_stats || []).filter(s => s.club_side === 'home');
});

const awayStats = computed(() => {
    return (props.game.player_stats || []).filter(s => s.club_side === 'away');
});

const submitPlayerStats = () => {
    playerStatsForm.post(route('organizer.matches.player-stats.store', props.game.id), {
        preserveScroll: true,
        onSuccess: () => {
            playerStatsForm.reset();
            showStatsForm.value = false;
        },
    });
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Score — ${game.home_club?.name || '?'} vs ${game.away_club?.name || '?'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('organizer.competitions.matches.index', game.phase?.competition?.id || game.phase?.competition_id)"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Feuille de match</h2>
                    <p class="text-xs text-gray-500">{{ game.phase?.competition?.name || '' }} &mdash; {{ game.phase?.name || '' }}</p>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Match header -->
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ formatDateTime(game.scheduled_at) }}</span>
                    <CompetitionBadge :status="game.status || 'scheduled'" />
                </div>

                <div class="flex items-center justify-center gap-6">
                    <div class="flex-1 text-right">
                        <p class="text-lg font-bold text-gray-900">{{ game.home_club?.name || '—' }}</p>
                        <p v-if="game.home_club?.city" class="text-xs text-gray-500">{{ game.home_club.city }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-900 px-5 py-3">
                        <span v-if="game.home_score !== null && game.away_score !== null" class="text-xl font-bold text-white">
                            {{ game.home_score }} - {{ game.away_score }}
                        </span>
                        <span v-else class="text-lg font-bold text-gray-400">VS</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-lg font-bold text-gray-900">{{ game.away_club?.name || '—' }}</p>
                        <p v-if="game.away_club?.city" class="text-xs text-gray-500">{{ game.away_club.city }}</p>
                    </div>
                </div>

                <p v-if="game.location" class="mt-4 text-center text-xs text-gray-400">{{ game.location }}</p>
            </div>

            <!-- Score form -->
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Saisie du score</h3>
                <form class="space-y-4" @submit.prevent="submitScore">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                {{ game.home_club?.name || 'Domicile' }}
                            </label>
                            <input
                                v-model.number="scoreForm.home_score"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-center text-lg font-bold focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="scoreForm.errors.home_score" class="mt-1 text-xs text-red-600">{{ scoreForm.errors.home_score }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Statut</label>
                            <select
                                v-model="scoreForm.status"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <p v-if="scoreForm.errors.status" class="mt-1 text-xs text-red-600">{{ scoreForm.errors.status }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                {{ game.away_club?.name || 'Exterieur' }}
                            </label>
                            <input
                                v-model.number="scoreForm.away_score"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-center text-lg font-bold focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="scoreForm.errors.away_score" class="mt-1 text-xs text-red-600">{{ scoreForm.errors.away_score }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="scoreForm.processing"
                        >
                            <svg v-if="scoreForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Enregistrer le score
                        </button>
                    </div>
                </form>
            </div>

            <!-- Player stats -->
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Statistiques joueurs</h3>
                    <button
                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                        @click="showStatsForm = !showStatsForm"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Add stats form -->
                <div v-if="showStatsForm" class="mb-6 rounded-lg border border-gray-100 bg-gray-50 p-4">
                    <form class="space-y-3" @submit.prevent="submitPlayerStats">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Joueur (ID)</label>
                                <input
                                    v-model="playerStatsForm.player_id"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Identifiant du joueur"
                                />
                                <p v-if="playerStatsForm.errors.player_id" class="mt-1 text-xs text-red-600">{{ playerStatsForm.errors.player_id }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Equipe</label>
                                <select
                                    v-model="playerStatsForm.club_side"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                                    <option value="home">{{ game.home_club?.name || 'Domicile' }}</option>
                                    <option value="away">{{ game.away_club?.name || 'Exterieur' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Buts</label>
                                <input
                                    v-model.number="playerStatsForm.goals"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Passes</label>
                                <input
                                    v-model.number="playerStatsForm.assists"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">C. jaunes</label>
                                <input
                                    v-model.number="playerStatsForm.yellow_cards"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">C. rouges</label>
                                <input
                                    v-model.number="playerStatsForm.red_cards"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Minutes</label>
                                <input
                                    v-model.number="playerStatsForm.minutes_played"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50"
                                @click="showStatsForm = false"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                                :disabled="playerStatsForm.processing || !playerStatsForm.player_id"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Stats tables -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Home team stats -->
                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ game.home_club?.name || 'Domicile' }}
                        </h4>
                        <div v-if="homeStats.length" class="overflow-hidden rounded-lg border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-100 text-xs">
                                <thead class="bg-gray-50/60">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-500">Joueur</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">B</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">P</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">CJ</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">CR</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">Min</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="stat in homeStats" :key="stat.id">
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ stat.player_name || stat.player_id }}</td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.goals }}</td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.assists }}</td>
                                        <td class="px-2 py-2 text-center">
                                            <span v-if="stat.yellow_cards" class="inline-block h-3 w-2 rounded-sm bg-yellow-400" />
                                            <span v-else class="text-gray-300">-</span>
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <span v-if="stat.red_cards" class="inline-block h-3 w-2 rounded-sm bg-red-500" />
                                            <span v-else class="text-gray-300">-</span>
                                        </td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.minutes_played }}'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="rounded-lg border border-dashed border-gray-200 py-4 text-center text-xs text-gray-400">
                            Aucune statistique
                        </p>
                    </div>

                    <!-- Away team stats -->
                    <div>
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ game.away_club?.name || 'Exterieur' }}
                        </h4>
                        <div v-if="awayStats.length" class="overflow-hidden rounded-lg border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-100 text-xs">
                                <thead class="bg-gray-50/60">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-500">Joueur</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">B</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">P</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">CJ</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">CR</th>
                                        <th class="px-2 py-2 text-center font-semibold text-gray-500">Min</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="stat in awayStats" :key="stat.id">
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ stat.player_name || stat.player_id }}</td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.goals }}</td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.assists }}</td>
                                        <td class="px-2 py-2 text-center">
                                            <span v-if="stat.yellow_cards" class="inline-block h-3 w-2 rounded-sm bg-yellow-400" />
                                            <span v-else class="text-gray-300">-</span>
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <span v-if="stat.red_cards" class="inline-block h-3 w-2 rounded-sm bg-red-500" />
                                            <span v-else class="text-gray-300">-</span>
                                        </td>
                                        <td class="px-2 py-2 text-center text-gray-600">{{ stat.minutes_played }}'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="rounded-lg border border-dashed border-gray-200 py-4 text-center text-xs text-gray-400">
                            Aucune statistique
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

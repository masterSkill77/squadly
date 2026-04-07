<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import StandingsTable from '@/Components/Competition/StandingsTable.vue';
import MatchCard from '@/Components/Competition/MatchCard.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';
import BracketTree from '@/Components/Competition/BracketTree.vue';

const props = defineProps({
    competition: Object,
    bracket: Object,
    knockoutPhase: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const activeTab = ref('classement');

const phases = computed(() => props.competition.phases ?? []);

const hasBracket = computed(() => props.bracket?.rounds?.length > 0);

const allGames = computed(() =>
    phases.value.flatMap((phase) => (phase.games ?? []).map((g) => ({ ...g, phaseName: phase.name }))),
);

const gamesByDate = computed(() => {
    const groups = {};
    for (const game of allGames.value) {
        const key = game.scheduled_at
            ? new Date(game.scheduled_at).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
            : 'Date non definie';
        if (!groups[key]) groups[key] = [];
        groups[key].push(game);
    }
    return groups;
});
</script>

<template>
    <Head :title="competition.name" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <!-- Content -->
        <div class="mx-auto max-w-5xl px-4 pt-28 pb-8 sm:px-6">
            <!-- Header -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ competition.name }}</h1>
                            <CompetitionBadge :status="competition.status" />
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                            <span v-if="competition.organizer" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                {{ competition.organizer.name }}
                            </span>
                            <span v-if="competition.season" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{ competition.season }}
                            </span>
                            <span v-if="competition.sport" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 10.875h-2.25A3.375 3.375 0 0 0 7.5 14.25v4.5m9 0H7.5" />
                                </svg>
                                {{ competition.sport }}
                            </span>
                            <span v-if="competition.clubs_count" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                {{ competition.clubs_count }} clubs
                            </span>
                        </div>

                        <p v-if="competition.description" class="mt-3 text-sm text-gray-600">
                            {{ competition.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-6 border-b border-gray-200">
                <nav class="-mb-px flex gap-6">
                    <button
                        v-for="tab in [
                            { key: 'classement', label: 'Classement' },
                            { key: 'matchs', label: 'Matchs' },
                            ...(hasBracket ? [{ key: 'tableau', label: 'Tableau' }] : []),
                        ]"
                        :key="tab.key"
                        class="whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-medium transition"
                        :class="activeTab === tab.key ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab content: Classement -->
            <div v-if="activeTab === 'classement'" class="mt-6 space-y-8">
                <template v-if="phases.length">
                    <div v-for="phase in phases" :key="phase.id">
                        <h3 class="mb-3 text-base font-semibold text-gray-900">{{ phase.name }}</h3>
                        <StandingsTable :standings="phase.standings ?? []" :qualify-count="phase.qualify_count ?? 0" />
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun classement disponible pour cette competition.</p>
                </div>
            </div>

            <!-- Tab content: Matchs -->
            <div v-if="activeTab === 'matchs'" class="mt-6 space-y-8">
                <template v-if="Object.keys(gamesByDate).length">
                    <div v-for="(games, date) in gamesByDate" :key="date">
                        <h3 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ date }}</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <MatchCard v-for="game in games" :key="game.id" :game="game" />
                        </div>
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun match programmé pour cette compétition.</p>
                </div>
            </div>

            <!-- Tab content: Tableau -->
            <div v-if="activeTab === 'tableau'" class="mt-6 space-y-4">
                <div v-if="knockoutPhase" class="flex items-center gap-3 rounded-xl bg-white px-5 py-3 shadow-sm">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 10.875h-2.25A3.375 3.375 0 0 0 7.5 14.25v4.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ knockoutPhase.name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ bracket.totalRounds }} tour(s) &middot;
                            {{ bracket.rounds.reduce((sum, r) => sum + r.games.length, 0) }} match(s)
                        </p>
                    </div>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <BracketTree :rounds="bracket.rounds" :total-rounds="bracket.totalRounds" />
                </div>
            </div>
        </div>
    </div>
</template>

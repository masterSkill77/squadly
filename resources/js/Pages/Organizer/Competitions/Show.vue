<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';
import StandingsTable from '@/Components/Competition/StandingsTable.vue';
import MatchCard from '@/Components/Competition/MatchCard.vue';
import BracketTree from '@/Components/Competition/BracketTree.vue';

const props = defineProps({
    competition: Object,
    bracket: Object,
    knockoutPhase: Object,
});

const activeTab = ref('info');

const hasBracket = computed(() => props.bracket?.rounds?.length > 0);

const tabs = computed(() => {
    const base = [
        { key: 'info', label: 'Informations' },
        { key: 'phases', label: 'Phases' },
        { key: 'clubs', label: 'Clubs inscrits' },
        { key: 'matches', label: 'Derniers matchs' },
        { key: 'standings', label: 'Classement' },
    ];
    if (hasBracket.value) {
        base.push({ key: 'bracket', label: 'Tableau' });
    }
    return base;
});

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

const phaseTypeLabels = {
    group: 'Phase de groupes',
    knockout: 'Elimination directe',
    round_robin: 'Aller-retour',
    final: 'Finale',
};

const registrationStatusLabels = {
    pending: 'En attente',
    approved: 'Approuve',
    rejected: 'Refuse',
};

const registrationStatusClasses = {
    pending: 'bg-amber-50 text-amber-700',
    approved: 'bg-emerald-50 text-emerald-700',
    rejected: 'bg-red-50 text-red-700',
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="competition.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('organizer.competitions.index')"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ competition.name }}</h2>
                    <p class="text-xs text-gray-500">{{ sportLabels[competition.sport_type] || competition.sport_type }} &mdash; {{ competition.season }}</p>
                </div>
                <CompetitionBadge :status="competition.status" />
            </div>
        </template>

        <div class="space-y-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex gap-6">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        class="relative border-b-2 pb-3 text-sm font-medium transition"
                        :class="activeTab === tab.key
                            ? 'border-emerald-600 text-emerald-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab: Info -->
            <div v-show="activeTab === 'info'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Details</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sport</dt>
                            <dd class="font-medium text-gray-900">{{ sportLabels[competition.sport_type] || competition.sport_type }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Format</dt>
                            <dd class="font-medium text-gray-900">{{ formatLabels[competition.format] || competition.format }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Saison</dt>
                            <dd class="font-medium text-gray-900">{{ competition.season }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Debut</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(competition.starts_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Fin</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(competition.ends_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Statut</dt>
                            <dd><CompetitionBadge :status="competition.status" /></dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Statistiques</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-emerald-50 p-4 text-center">
                            <p class="text-2xl font-bold text-emerald-700">{{ competition.phases?.length || 0 }}</p>
                            <p class="text-xs text-emerald-600">Phases</p>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-4 text-center">
                            <p class="text-2xl font-bold text-blue-700">{{ competition.competition_clubs?.length || 0 }}</p>
                            <p class="text-xs text-blue-600">Clubs</p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-purple-50 p-4 text-center">
                            <p class="text-2xl font-bold text-purple-700">
                                {{ competition.phases?.reduce((sum, p) => sum + (p.games?.length || 0), 0) || 0 }}
                            </p>
                            <p class="text-xs text-purple-600">Matchs programmes</p>
                        </div>
                    </div>
                    <div v-if="competition.description" class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500">Description</p>
                        <p class="mt-1 text-sm text-gray-700">{{ competition.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Tab: Phases -->
            <div v-show="activeTab === 'phases'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Phases ({{ competition.phases?.length || 0 }})</h3>
                    <Link
                        :href="route('organizer.competitions.phases.index', competition.id)"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        Gerer les phases
                    </Link>
                </div>

                <div v-if="competition.phases?.length" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="phase in competition.phases"
                        :key="phase.id"
                        class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ phase.name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ phaseTypeLabels[phase.type] || phase.type }}</p>
                            </div>
                            <CompetitionBadge :status="phase.status || 'draft'" />
                        </div>
                        <p class="mt-3 text-xs text-gray-400">{{ phase.games?.length || 0 }} match(s)</p>
                    </div>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm text-gray-400">Aucune phase configuree.</p>
                </div>
            </div>

            <!-- Tab: Clubs -->
            <div v-show="activeTab === 'clubs'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Clubs inscrits ({{ competition.competition_clubs?.length || 0 }})</h3>
                    <Link
                        :href="route('organizer.competitions.clubs.index', competition.id)"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        Gerer les clubs
                    </Link>
                </div>

                <div v-if="competition.competition_clubs?.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Club</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="cc in competition.competition_clubs" :key="cc.id" class="transition hover:bg-gray-50/50">
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ cc.club?.name || '—' }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="registrationStatusClasses[cc.status] || 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ registrationStatusLabels[cc.status] || cc.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ cc.phase?.name || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm text-gray-400">Aucun club inscrit.</p>
                </div>
            </div>

            <!-- Tab: Matches -->
            <div v-show="activeTab === 'matches'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Derniers matchs</h3>
                    <Link
                        :href="route('organizer.competitions.matches.index', competition.id)"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        Voir tous les matchs
                    </Link>
                </div>

                <div v-if="competition.phases?.some(p => p.games?.length)" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template v-for="phase in competition.phases" :key="phase.id">
                        <MatchCard
                            v-for="game in (phase.games || []).slice(0, 6)"
                            :key="game.id"
                            :game="game"
                            :phase-name="phase.name"
                        />
                    </template>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm text-gray-400">Aucun match programme.</p>
                </div>
            </div>

            <!-- Tab: Standings -->
            <div v-show="activeTab === 'standings'" class="space-y-6">
                <template v-if="competition.phases?.some(p => p.standings?.length)">
                    <div v-for="phase in competition.phases" :key="phase.id">
                        <h3 v-if="competition.phases.length > 1" class="mb-3 text-sm font-semibold text-gray-900">{{ phase.name }}</h3>
                        <StandingsTable v-if="phase.standings?.length" :standings="phase.standings" :qualify-count="phase.qualify_count ?? 0" />
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm text-gray-400">Aucun classement disponible.</p>
                </div>
            </div>

            <!-- Tab: Bracket -->
            <div v-if="hasBracket" v-show="activeTab === 'bracket'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Tableau du tournoi</h3>
                    <Link
                        :href="route('organizer.competitions.bracket', competition.id)"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        Voir en plein écran
                    </Link>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <BracketTree
                        :rounds="bracket.rounds"
                        :total-rounds="bracket.totalRounds"
                    />
                </div>
            </div>

            <!-- Action links -->
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6">
                <!-- Modifier : toujours sauf si terminée -->
                <Link
                    v-if="competition.status !== 'finished'"
                    :href="route('organizer.competitions.edit', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                    </svg>
                    Modifier
                </Link>

                <!-- Gérer les phases : tant que pas terminée -->
                <Link
                    v-if="competition.status !== 'finished'"
                    :href="route('organizer.competitions.phases.index', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Gérer les phases
                </Link>

                <!-- Tirage au sort : quand il y a des clubs approuvés mais pas tous assignés -->
                <Link
                    v-if="competition.status !== 'finished' && competition.competition_clubs?.some(c => c.status === 'approved' && !c.phase_id)"
                    :href="route('organizer.competitions.draw', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" /></svg>
                    Tirage au sort
                </Link>

                <!-- Gérer les matchs : quand il y a des matchs -->
                <Link
                    v-if="competition.phases?.some(p => p.games?.length)"
                    :href="route('organizer.competitions.matches.index', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Gérer les matchs
                </Link>

                <!-- Voir le tableau : quand un bracket existe -->
                <Link
                    v-if="hasBracket"
                    :href="route('organizer.competitions.bracket', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
                    </svg>
                    Voir le tableau
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

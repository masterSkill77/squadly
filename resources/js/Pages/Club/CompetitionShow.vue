<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StandingsTable from '@/Components/Competition/StandingsTable.vue';
import MatchCard from '@/Components/Competition/MatchCard.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

const props = defineProps({
    competition: Object,
    club: Object,
    myGames: Array,
});

const activeTab = ref('classement');

const phases = computed(() => props.competition.phases ?? []);

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head :title="competition.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('club.competitions')"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ competition.name }}</h2>
                    <p class="text-xs text-gray-500">
                        {{ competition.organizer?.name }} — {{ competition.season }}
                    </p>
                </div>
                <CompetitionBadge :status="competition.status" />
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info cards -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-bold text-gray-900">{{ competition.clubs_count || 0 }}</p>
                    <p class="text-xs text-gray-500">Clubs</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-bold text-gray-900">{{ phases.length }}</p>
                    <p class="text-xs text-gray-500">Phases</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-bold text-gray-900">{{ myGames?.length || 0 }}</p>
                    <p class="text-xs text-gray-500">Nos matchs</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
                    <p class="text-xs text-gray-500">Période</p>
                    <p class="mt-1 text-xs font-medium text-gray-700">{{ formatDate(competition.starts_at) }}</p>
                    <p class="text-xs text-gray-400">→ {{ formatDate(competition.ends_at) }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex gap-6">
                    <button
                        v-for="tab in [
                            { key: 'classement', label: 'Classement' },
                            { key: 'matchs', label: 'Nos matchs' },
                            { key: 'calendrier', label: 'Tous les matchs' },
                        ]"
                        :key="tab.key"
                        class="relative border-b-2 pb-3 text-sm font-medium transition"
                        :class="activeTab === tab.key ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab: Classement -->
            <div v-show="activeTab === 'classement'" class="space-y-6">
                <template v-if="phases.some(p => p.standings?.length)">
                    <div v-for="phase in phases" :key="phase.id">
                        <h3 v-if="phases.length > 1" class="mb-3 text-sm font-semibold text-gray-900">{{ phase.name }}</h3>
                        <StandingsTable v-if="phase.standings?.length" :standings="phase.standings" />
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun classement disponible.</p>
                </div>
            </div>

            <!-- Tab: Nos matchs -->
            <div v-show="activeTab === 'matchs'" class="space-y-4">
                <div v-if="myGames?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <MatchCard v-for="game in myGames" :key="game.id" :game="game" />
                </div>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun match pour {{ club.name }}.</p>
                </div>
            </div>

            <!-- Tab: Tous les matchs -->
            <div v-show="activeTab === 'calendrier'" class="space-y-6">
                <template v-if="phases.some(p => p.games?.length)">
                    <div v-for="phase in phases" :key="phase.id" class="space-y-3">
                        <h3 v-if="phases.length > 1" class="text-sm font-semibold text-gray-900">{{ phase.name }}</h3>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <MatchCard v-for="game in phase.games" :key="game.id" :game="game" />
                        </div>
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun match programmé.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

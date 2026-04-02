<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MatchCard from '@/Components/Competition/MatchCard.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

const props = defineProps({
    club: Object,
    myCompetitions: Array,
    upcomingGames: Array,
    allCompetitions: Object, // paginated
    registeredIds: Array,
    pendingIds: Array,
    search: String,
    clubTeams: Array,
    canRegister: Boolean,
});

const activeTab = ref('my');
const searchQuery = ref(props.search || '');
const confirmingCompetition = ref(null);
const selectedTeamId = ref('');

const sportEmojis = {
    football: '⚽', basketball: '🏀', handball: '🤾',
    natation: '🏊', rugby: '🏉', volleyball: '🏐',
};

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Debounced search
let searchTimeout = null;
watch(searchQuery, (val) => {
    clearTimeout(searchTimeout);
    if (val.length >= 4 || val.length === 0) {
        searchTimeout = setTimeout(() => {
            router.get(route('club.competitions'), { search: val || undefined }, {
                preserveState: true,
                preserveScroll: true,
                only: ['allCompetitions', 'registeredIds', 'pendingIds', 'search'],
            });
        }, 300);
    }
});

function goToPage(url) {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

function isRegistered(compId) {
    return props.registeredIds?.includes(compId);
}

function isPending(compId) {
    return props.pendingIds?.includes(compId);
}

function confirmRegister(comp) {
    confirmingCompetition.value = comp;
    selectedTeamId.value = '';
}

function register() {
    if (!confirmingCompetition.value || !selectedTeamId.value) return;
    router.post(route('competitions.register', confirmingCompetition.value.id), {
        team_id: selectedTeamId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingCompetition.value = null;
            selectedTeamId.value = '';
        },
    });
}

// Filter teams matching competition sport type
function teamsForCompetition(comp) {
    if (!comp?.sport_type || !props.clubTeams) return props.clubTeams || [];
    return props.clubTeams.filter(t => t.section?.sport_type === comp.sport_type);
}

const pivotStatusLabels = {
    pending: 'En attente',
    approved: 'Inscrit',
    rejected: 'Refusé',
};

const pivotStatusClasses = {
    pending: 'bg-amber-50 text-amber-700',
    approved: 'bg-emerald-50 text-emerald-700',
    rejected: 'bg-red-50 text-red-700',
};
</script>

<template>
    <Head title="Compétitions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Compétitions</h2>
        </template>

        <div class="space-y-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex gap-6">
                    <button
                        class="relative border-b-2 pb-3 text-sm font-medium transition"
                        :class="activeTab === 'my' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        @click="activeTab = 'my'"
                    >
                        Mes compétitions
                        <span v-if="myCompetitions?.length" class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ myCompetitions.length }}</span>
                    </button>
                    <button
                        class="relative border-b-2 pb-3 text-sm font-medium transition"
                        :class="activeTab === 'browse' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        @click="activeTab = 'browse'"
                    >
                        Trouver une compétition
                    </button>
                </nav>
            </div>

            <!-- Tab: Mes compétitions -->
            <div v-show="activeTab === 'my'" class="space-y-8">
                <!-- My competitions -->
                <section>
                    <div v-if="myCompetitions?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="comp in myCompetitions"
                            :key="comp.id"
                            :href="route('club.competitions.show', comp.id)"
                            class="group rounded-xl border border-gray-100 bg-white p-5 transition hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xl">{{ sportEmojis[comp.sport_type] || '🏅' }}</span>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="pivotStatusClasses[comp.pivot?.status] || 'bg-gray-100 text-gray-600'"
                                >
                                    {{ pivotStatusLabels[comp.pivot?.status] || comp.pivot?.status }}
                                </span>
                            </div>
                            <h4 class="mt-3 font-semibold text-gray-900 group-hover:text-emerald-700">{{ comp.name }}</h4>
                            <p v-if="comp.organizer" class="mt-0.5 text-xs text-gray-500">{{ comp.organizer.name }}</p>
                            <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                                <span>{{ comp.season }}</span>
                                <CompetitionBadge :status="comp.status" />
                            </div>
                        </Link>
                    </div>
                    <div v-else class="rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-3.52 1.122 6.023 6.023 0 0 1-3.52-1.122" /></svg>
                        <p class="mt-3 text-sm text-gray-500">Votre club ne participe à aucune compétition.</p>
                        <button class="mt-3 text-sm font-medium text-emerald-600 hover:text-emerald-700" @click="activeTab = 'browse'">
                            Trouver une compétition →
                        </button>
                    </div>
                </section>

                <!-- Upcoming games -->
                <section v-if="upcomingGames?.length">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">Prochains matchs</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <MatchCard v-for="game in upcomingGames" :key="game.id" :game="game" />
                    </div>
                </section>
            </div>

            <!-- Tab: Browse -->
            <div v-show="activeTab === 'browse'" class="space-y-6">
                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher une compétition (min. 4 caractères)…"
                        class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 shadow-sm transition focus:border-emerald-500 focus:ring-emerald-500"
                    />
                    <span v-if="searchQuery.length > 0 && searchQuery.length < 4" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-500">
                        min. {{ 4 - searchQuery.length }} caractère(s)
                    </span>
                    <span v-if="searchQuery.length >= 4" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                        {{ allCompetitions?.total || 0 }} résultat(s)
                    </span>
                </div>

                <!-- Results -->
                <div v-if="allCompetitions?.data?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="comp in allCompetitions.data"
                        :key="comp.id"
                        class="rounded-xl border border-gray-100 bg-white p-5 transition hover:shadow-md"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xl">{{ sportEmojis[comp.sport_type] || '🏅' }}</span>
                            <CompetitionBadge :status="comp.status" />
                        </div>
                        <h4 class="mt-3 font-semibold text-gray-900">{{ comp.name }}</h4>
                        <p v-if="comp.organizer" class="mt-0.5 text-xs text-gray-500">{{ comp.organizer.name }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400">
                            <span>{{ comp.season }}</span>
                            <span>{{ comp.clubs_count || 0 }} clubs</span>
                            <span>{{ formatDate(comp.starts_at) }} → {{ formatDate(comp.ends_at) }}</span>
                        </div>
                        <p v-if="comp.description" class="mt-2 text-xs text-gray-500 line-clamp-2">{{ comp.description }}</p>

                        <!-- Action -->
                        <div class="mt-4">
                            <Link
                                v-if="isRegistered(comp.id) && !isPending(comp.id)"
                                :href="route('club.competitions.show', comp.id)"
                                class="block w-full rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm font-medium text-emerald-700"
                            >
                                Déjà inscrit — Voir
                            </Link>
                            <button
                                v-else-if="isPending(comp.id)"
                                disabled
                                class="w-full cursor-not-allowed rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700"
                            >
                                Inscription en attente…
                            </button>
                            <button
                                v-else-if="canRegister"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                @click="confirmRegister(comp)"
                            >
                                S'inscrire
                            </button>
                            <p v-else class="text-center text-xs text-gray-400">Seul le président peut inscrire le club.</p>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div v-if="allCompetitions?.last_page > 1" class="flex items-center justify-between pt-2">
                    <p class="text-xs text-gray-500">
                        Page {{ allCompetitions.current_page }} / {{ allCompetitions.last_page }}
                        — {{ allCompetitions.total }} compétition(s)
                    </p>
                    <div class="flex gap-2">
                        <button
                            v-for="link in allCompetitions.links"
                            :key="link.label"
                            :disabled="!link.url"
                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                : link.url
                                    ? 'border-gray-200 text-gray-600 hover:bg-gray-50'
                                    : 'border-gray-100 text-gray-300 cursor-not-allowed'"
                            @click="goToPage(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <div v-if="!allCompetitions?.data?.length" class="rounded-xl border border-dashed border-gray-300 p-12 text-center">
                    <p class="text-sm text-gray-500">
                        {{ searchQuery.length >= 4 ? 'Aucune compétition trouvée.' : 'Aucune compétition disponible pour le moment.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Confirmation modal -->
        <Teleport to="body">
            <div v-if="confirmingCompetition" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="confirmingCompetition = null">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900">Confirmer l'inscription</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Voulez-vous inscrire <strong>{{ club.name }}</strong> à la compétition <strong>{{ confirmingCompetition.name }}</strong> ?
                    </p>
                    <p class="mt-1 text-xs text-gray-400">L'organisateur devra approuver votre demande.</p>

                    <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sport</span>
                            <span class="font-medium text-gray-900">{{ confirmingCompetition.sport_type }}</span>
                        </div>
                        <div class="mt-1 flex justify-between">
                            <span class="text-gray-500">Saison</span>
                            <span class="font-medium text-gray-900">{{ confirmingCompetition.season }}</span>
                        </div>
                        <div class="mt-1 flex justify-between">
                            <span class="text-gray-500">Organisateur</span>
                            <span class="font-medium text-gray-900">{{ confirmingCompetition.organizer?.name }}</span>
                        </div>
                    </div>

                    <!-- Team selection -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Équipe à inscrire</label>
                        <select
                            v-model="selectedTeamId"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choisir une équipe</option>
                            <option
                                v-for="team in teamsForCompetition(confirmingCompetition)"
                                :key="team.id"
                                :value="team.id"
                            >
                                {{ team.name }}<template v-if="team.age_category"> — {{ team.age_category }}</template>
                            </option>
                        </select>
                        <p v-if="teamsForCompetition(confirmingCompetition).length === 0" class="mt-1 text-xs text-red-500">
                            Aucune équipe disponible pour ce sport. Créez d'abord une équipe dans la section {{ confirmingCompetition.sport_type }}.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            @click="confirmingCompetition = null"
                        >
                            Annuler
                        </button>
                        <button
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="!selectedTeamId"
                            @click="register"
                        >
                            Confirmer l'inscription
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

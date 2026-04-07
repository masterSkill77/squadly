<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    organizer: Object,
});

const step = ref(1);
const totalSteps = 4;

const form = useForm({
    name: '',
    sport_type: '',
    season: '',
    format: '',
    description: '',
    starts_at: '',
    ends_at: '',
    // Config format
    group_count: 2,
    teams_per_group: 4,
    qualify_count: 2,
    expected_teams: 16,
    round_trip: false, // aller-retour en championnat
    playoff_qualify_count: 8, // top X pour les playoffs
});

// ─── Sport options (collectifs uniquement pour l'instant) ───
const sportOptions = [
    { value: 'football', label: 'Football', icon: '⚽' },
    { value: 'basketball', label: 'Basketball', icon: '🏀' },
    { value: 'handball', label: 'Handball', icon: '🤾' },
    { value: 'volleyball', label: 'Volleyball', icon: '🏐' },
    { value: 'rugby', label: 'Rugby', icon: '🏉' },
];

const disabledSports = [
    { value: 'natation', label: 'Natation', icon: '🏊', soon: true },
];

// ─── Format options ───
const formatOptions = [
    {
        value: 'league',
        label: 'Championnat',
        description: 'Tous contre tous, un classement final.',
        example: 'Liga, Ligue 1, Pro B...',
    },
    {
        value: 'cup',
        label: 'Coupe',
        description: 'Élimination directe, un seul gagnant.',
        example: 'Coupe de Madagascar, FA Cup...',
    },
    {
        value: 'group_knockout',
        label: 'Poules + Élimination',
        description: 'Phase de poules puis les meilleurs s\'affrontent.',
        example: 'CAN, Coupe du Monde...',
    },
    {
        value: 'league_playoffs',
        label: 'Championnat + Playoffs',
        description: 'Saison régulière puis les meilleurs en élimination directe.',
        example: 'Champions League, NBA, PBA...',
    },
];

// ─── Computed ───
const selectedSport = computed(() =>
    sportOptions.find(s => s.value === form.sport_type),
);

const selectedFormat = computed(() =>
    formatOptions.find(f => f.value === form.format),
);

const canProceedStep1 = computed(() => form.sport_type && form.format);

const canProceedStep2 = computed(() =>
    form.name && form.season && form.starts_at && form.ends_at,
);

const canProceedStep3 = computed(() => {
    if (form.format === 'league') return true;
    if (form.format === 'cup') return form.expected_teams >= 2;
    if (form.format === 'group_knockout') {
        return form.group_count >= 2 && form.teams_per_group >= 2 && form.qualify_count >= 1;
    }
    if (form.format === 'league_playoffs') return form.playoff_qualify_count >= 2;
    return false;
});

// Total qualified teams for group_knockout
const totalQualified = computed(() => form.group_count * form.qualify_count);

// Bracket rounds for cup, group_knockout and league_playoffs
const bracketRounds = computed(() => {
    const teams = form.format === 'cup' ? form.expected_teams
        : form.format === 'league_playoffs' ? form.playoff_qualify_count
        : totalQualified.value;
    if (teams < 2) return [];

    let size = 1;
    while (size < teams) size *= 2;

    const rounds = [];
    let count = size / 2;
    const totalR = Math.round(Math.log(size) / Math.log(2));

    for (let r = totalR; r >= 1; r--) {
        const label = r === 1 ? 'Finale'
            : r === 2 ? 'Demi-finales'
            : r === 3 ? 'Quarts de finale'
            : r === 4 ? 'Huitièmes de finale'
            : r === 5 ? 'Seizièmes de finale'
            : `Tour ${r}`;
        rounds.push({ round: r, label, games: count });
        count = Math.floor(count / 2);
    }
    return rounds;
});

// Total teams in group_knockout
const totalTeams = computed(() => form.group_count * form.teams_per_group);

// Structure summary for recap
const structureSummary = computed(() => {
    if (form.format === 'league') {
        return form.round_trip ? 'Aller-retour (chaque équipe joue 2 fois contre chaque adversaire)' : 'Aller simple (chaque équipe joue 1 fois contre chaque adversaire)';
    }
    if (form.format === 'cup') {
        return `${form.expected_teams} équipes en élimination directe`;
    }
    if (form.format === 'group_knockout') {
        return `${form.group_count} poules de ${form.teams_per_group} → ${totalQualified.value} qualifiés → ${bracketRounds.value[0]?.label || 'Finale'}`;
    }
    if (form.format === 'league_playoffs') {
        const mode = form.round_trip ? 'aller-retour' : 'aller simple';
        return `Championnat (${mode}) → Top ${form.playoff_qualify_count} en playoffs → ${bracketRounds.value[0]?.label || 'Finale'}`;
    }
    return '';
});

// Phases that will be auto-generated
const generatedPhases = computed(() => {
    const phases = [];
    if (form.format === 'league') {
        phases.push({ name: 'Championnat', type: form.round_trip ? 'round_robin' : 'group' });
    } else if (form.format === 'cup') {
        phases.push({ name: 'Tableau principal', type: 'knockout' });
    } else if (form.format === 'group_knockout') {
        for (let i = 0; i < form.group_count; i++) {
            const letter = String.fromCharCode(65 + i);
            phases.push({ name: `Poule ${letter}`, type: 'group', qualify_count: form.qualify_count });
        }
        phases.push({ name: 'Phase finale', type: 'knockout' });
    } else if (form.format === 'league_playoffs') {
        phases.push({
            name: 'Saison régulière',
            type: form.round_trip ? 'round_robin' : 'group',
            qualify_count: form.playoff_qualify_count,
        });
        phases.push({ name: 'Playoffs', type: 'knockout' });
    }
    return phases;
});

// ─── Navigation ───
const nextStep = () => {
    if (step.value < totalSteps) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submit = () => {
    form.transform((data) => ({
        name: data.name,
        sport_type: data.sport_type,
        season: data.season,
        format: data.format,
        description: data.description,
        starts_at: data.starts_at,
        ends_at: data.ends_at,
        config: {
            group_count: data.group_count,
            teams_per_group: data.teams_per_group,
            qualify_count: data.qualify_count,
            expected_teams: data.expected_teams,
            round_trip: data.round_trip,
            playoff_qualify_count: data.playoff_qualify_count,
        },
        phases: generatedPhases.value,
    })).post(route('organizer.competitions.store'));
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const stepLabels = ['Sport & Format', 'Informations', 'Configuration', 'Récapitulatif'];
</script>

<template>
    <Head title="Nouvelle compétition" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Nouvelle compétition</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <!-- Step indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div v-for="s in totalSteps" :key="s" class="flex items-center" :class="s < totalSteps ? 'flex-1' : ''">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition"
                                :class="s <= step ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-400'"
                            >
                                <svg v-if="s < step" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span v-else>{{ s }}</span>
                            </div>
                            <span class="hidden text-sm font-medium sm:inline" :class="s <= step ? 'text-gray-900' : 'text-gray-400'">
                                {{ stepLabels[s - 1] }}
                            </span>
                        </div>
                        <div v-if="s < totalSteps" class="mx-4 h-px flex-1 bg-gray-200">
                            <div class="h-full bg-emerald-600 transition-all duration-300" :style="{ width: step > s ? '100%' : '0%' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════════════════ Step 1: Sport & Format ════════════════ -->
            <div v-show="step === 1" class="space-y-6">
                <!-- Sport -->
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Quel sport ?</h3>
                    <p class="mb-5 text-sm text-gray-500">Choisissez le sport de votre compétition.</p>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <button
                            v-for="sport in sportOptions"
                            :key="sport.value"
                            type="button"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-center transition"
                            :class="form.sport_type === sport.value
                                ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500'
                                : 'border-gray-100 hover:border-gray-300 hover:bg-gray-50'"
                            @click="form.sport_type = sport.value"
                        >
                            <span class="text-2xl">{{ sport.icon }}</span>
                            <span class="text-sm font-medium" :class="form.sport_type === sport.value ? 'text-emerald-700' : 'text-gray-700'">
                                {{ sport.label }}
                            </span>
                        </button>

                        <!-- Disabled sports -->
                        <div
                            v-for="sport in disabledSports"
                            :key="sport.value"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 p-4 text-center opacity-50"
                        >
                            <span class="text-2xl grayscale">{{ sport.icon }}</span>
                            <span class="text-sm font-medium text-gray-400">{{ sport.label }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-400">Bientôt</span>
                        </div>
                    </div>
                </div>

                <!-- Format -->
                <div v-if="form.sport_type" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Quel format ?</h3>
                    <p class="mb-5 text-sm text-gray-500">Comment se déroulera votre compétition ?</p>

                    <div class="space-y-3">
                        <button
                            v-for="fmt in formatOptions"
                            :key="fmt.value"
                            type="button"
                            class="flex w-full items-start gap-4 rounded-xl border-2 p-4 text-left transition"
                            :class="form.format === fmt.value
                                ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500'
                                : 'border-gray-100 hover:border-gray-300 hover:bg-gray-50'"
                            @click="form.format = fmt.value"
                        >
                            <div class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2 transition"
                                :class="form.format === fmt.value ? 'border-emerald-500' : 'border-gray-300'"
                            >
                                <div v-if="form.format === fmt.value" class="h-2.5 w-2.5 rounded-full bg-emerald-500" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold" :class="form.format === fmt.value ? 'text-emerald-700' : 'text-gray-900'">
                                    {{ fmt.label }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ fmt.description }}</p>
                                <p class="mt-1 text-[11px] text-gray-400 italic">Ex : {{ fmt.example }}</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ════════════════ Step 2: Infos ════════════════ -->
            <div v-show="step === 2" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-1 text-base font-semibold text-gray-900">Informations de la compétition</h3>
                <p class="mb-5 text-sm text-gray-500">Les détails de votre {{ selectedFormat?.label?.toLowerCase() }}.</p>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nom de la compétition *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            :placeholder="form.format === 'league' ? 'Ex : Championnat régional U17' : form.format === 'cup' ? 'Ex : Coupe de Madagascar' : 'Ex : Champions League Mada'"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Saison *</label>
                        <input
                            v-model="form.season"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ex : 2025-2026"
                        />
                        <p v-if="form.errors.season" class="mt-1 text-xs text-red-600">{{ form.errors.season }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date de début *</label>
                            <input
                                v-model="form.starts_at"
                                type="date"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="form.errors.starts_at" class="mt-1 text-xs text-red-600">{{ form.errors.starts_at }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date de fin *</label>
                            <input
                                v-model="form.ends_at"
                                type="date"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="form.errors.ends_at" class="mt-1 text-xs text-red-600">{{ form.errors.ends_at }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Description optionnelle..."
                        />
                    </div>
                </div>
            </div>

            <!-- ════════════════ Step 3: Configuration ════════════════ -->
            <div v-show="step === 3" class="space-y-6">
                <!-- Championnat -->
                <div v-if="form.format === 'league'" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Configuration du championnat</h3>
                    <p class="mb-5 text-sm text-gray-500">Comment les équipes s'affrontent ?</p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-700">Mode de rencontre</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <button
                                    type="button"
                                    class="rounded-xl border-2 p-4 text-left transition"
                                    :class="!form.round_trip ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 hover:border-gray-300'"
                                    @click="form.round_trip = false"
                                >
                                    <p class="text-sm font-semibold" :class="!form.round_trip ? 'text-emerald-700' : 'text-gray-900'">Aller simple</p>
                                    <p class="mt-1 text-xs text-gray-500">Chaque équipe joue 1 fois contre chaque adversaire.</p>
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border-2 p-4 text-left transition"
                                    :class="form.round_trip ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 hover:border-gray-300'"
                                    @click="form.round_trip = true"
                                >
                                    <p class="text-sm font-semibold" :class="form.round_trip ? 'text-emerald-700' : 'text-gray-900'">Aller-retour</p>
                                    <p class="mt-1 text-xs text-gray-500">Chaque équipe joue 2 fois (domicile + extérieur).</p>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coupe -->
                <div v-if="form.format === 'cup'" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Configuration de la coupe</h3>
                    <p class="mb-5 text-sm text-gray-500">Combien d'équipes participent ?</p>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre d'équipes attendues</label>
                        <input
                            v-model.number="form.expected_teams"
                            type="number"
                            min="2"
                            max="128"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <p class="mt-1 text-xs text-gray-400">Le tableau sera adapté automatiquement (exemptions si nécessaire).</p>
                    </div>

                    <!-- Bracket preview -->
                    <div v-if="bracketRounds.length" class="mt-5 rounded-lg bg-gray-50 p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Aperçu du tableau</p>
                        <div class="flex items-center gap-2">
                            <div
                                v-for="(round, i) in bracketRounds"
                                :key="round.round"
                                class="flex items-center gap-2"
                            >
                                <div class="rounded-lg bg-white px-3 py-2 text-center shadow-sm">
                                    <p class="text-xs font-semibold text-gray-700">{{ round.label }}</p>
                                    <p class="text-[10px] text-gray-400">{{ round.games }} match(s)</p>
                                </div>
                                <svg v-if="i < bracketRounds.length - 1" class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poules + Elimination -->
                <div v-if="form.format === 'group_knockout'" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Configuration des poules</h3>
                    <p class="mb-5 text-sm text-gray-500">Organisez vos poules et la qualification.</p>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre de poules</label>
                                <input
                                    v-model.number="form.group_count"
                                    type="number"
                                    min="2"
                                    max="16"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Équipes par poule</label>
                                <input
                                    v-model.number="form.teams_per_group"
                                    type="number"
                                    min="2"
                                    max="12"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Qualifiés par poule</label>
                                <input
                                    v-model.number="form.qualify_count"
                                    type="number"
                                    min="1"
                                    :max="form.teams_per_group"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Visual summary -->
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Structure de la compétition</p>

                            <!-- Groups -->
                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    v-for="i in form.group_count"
                                    :key="i"
                                    class="rounded-lg bg-white px-3 py-2 text-center shadow-sm"
                                >
                                    <p class="text-xs font-semibold text-gray-700">Poule {{ String.fromCharCode(64 + i) }}</p>
                                    <p class="text-[10px] text-gray-400">{{ form.teams_per_group }} équipes</p>
                                    <p class="text-[10px] font-medium text-emerald-600">Top {{ form.qualify_count }} qualifié(s)</p>
                                </div>
                            </div>

                            <!-- Arrow -->
                            <div class="my-3 flex items-center justify-center">
                                <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                </svg>
                                <span class="ml-2 text-xs font-medium text-gray-500">{{ totalQualified }} équipes qualifiées</span>
                            </div>

                            <!-- Bracket preview -->
                            <div v-if="bracketRounds.length" class="flex flex-wrap items-center gap-2">
                                <div
                                    v-for="(round, i) in bracketRounds"
                                    :key="round.round"
                                    class="flex items-center gap-2"
                                >
                                    <div class="rounded-lg bg-white px-3 py-2 text-center shadow-sm">
                                        <p class="text-xs font-semibold text-gray-700">{{ round.label }}</p>
                                        <p class="text-[10px] text-gray-400">{{ round.games }} match(s)</p>
                                    </div>
                                    <svg v-if="i < bracketRounds.length - 1" class="h-4 w-4 flex-shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-gray-400">
                            Total : <strong>{{ totalTeams }}</strong> équipes &middot;
                            <strong>{{ form.group_count }}</strong> poules &middot;
                            <strong>{{ totalQualified }}</strong> qualifiés pour la phase finale
                        </p>
                    </div>
                </div>

                <!-- Championnat + Playoffs -->
                <div v-if="form.format === 'league_playoffs'" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-1 text-base font-semibold text-gray-900">Configuration du championnat + playoffs</h3>
                    <p class="mb-5 text-sm text-gray-500">Saison régulière puis les meilleurs en élimination directe.</p>

                    <div class="space-y-4">
                        <!-- Mode aller/retour -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-700">Saison régulière</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <button
                                    type="button"
                                    class="rounded-xl border-2 p-4 text-left transition"
                                    :class="!form.round_trip ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 hover:border-gray-300'"
                                    @click="form.round_trip = false"
                                >
                                    <p class="text-sm font-semibold" :class="!form.round_trip ? 'text-emerald-700' : 'text-gray-900'">Aller simple</p>
                                    <p class="mt-1 text-xs text-gray-500">Chaque équipe joue 1 fois contre chaque adversaire.</p>
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border-2 p-4 text-left transition"
                                    :class="form.round_trip ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 hover:border-gray-300'"
                                    @click="form.round_trip = true"
                                >
                                    <p class="text-sm font-semibold" :class="form.round_trip ? 'text-emerald-700' : 'text-gray-900'">Aller-retour</p>
                                    <p class="mt-1 text-xs text-gray-500">Chaque équipe joue 2 fois (domicile + extérieur).</p>
                                </button>
                            </div>
                        </div>

                        <!-- Nombre de qualifiés -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Combien d'équipes en playoffs ?</label>
                            <input
                                v-model.number="form.playoff_qualify_count"
                                type="number"
                                min="2"
                                max="32"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-48"
                            />
                            <p class="mt-1 text-xs text-gray-400">Les X premiers du classement accèdent aux playoffs.</p>
                        </div>

                        <!-- Visual preview -->
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Structure de la compétition</p>

                            <!-- League phase -->
                            <div class="rounded-lg bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs font-semibold text-gray-700">Saison régulière</p>
                                <p class="text-[10px] text-gray-400">{{ form.round_trip ? 'Aller-retour' : 'Aller simple' }} — Classement unique</p>
                                <p class="text-[10px] font-medium text-emerald-600">Top {{ form.playoff_qualify_count }} qualifié(s)</p>
                            </div>

                            <!-- Arrow -->
                            <div class="my-3 flex items-center justify-center">
                                <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                </svg>
                                <span class="ml-2 text-xs font-medium text-gray-500">{{ form.playoff_qualify_count }} équipes en playoffs</span>
                            </div>

                            <!-- Bracket preview -->
                            <div v-if="bracketRounds.length" class="flex flex-wrap items-center gap-2">
                                <div
                                    v-for="(round, i) in bracketRounds"
                                    :key="round.round"
                                    class="flex items-center gap-2"
                                >
                                    <div class="rounded-lg bg-white px-3 py-2 text-center shadow-sm">
                                        <p class="text-xs font-semibold text-gray-700">{{ round.label }}</p>
                                        <p class="text-[10px] text-gray-400">{{ round.games }} match(s)</p>
                                    </div>
                                    <svg v-if="i < bracketRounds.length - 1" class="h-4 w-4 flex-shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════════════════ Step 4: Recap ════════════════ -->
            <div v-show="step === 4" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-5 text-base font-semibold text-gray-900">Récapitulatif</h3>

                <div class="space-y-4">
                    <!-- Info -->
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Compétition</h4>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <dt class="text-gray-500">Nom</dt>
                            <dd class="font-medium text-gray-900">{{ form.name }}</dd>

                            <dt class="text-gray-500">Sport</dt>
                            <dd class="font-medium text-gray-900">{{ selectedSport?.icon }} {{ selectedSport?.label }}</dd>

                            <dt class="text-gray-500">Format</dt>
                            <dd class="font-medium text-gray-900">{{ selectedFormat?.label }}</dd>

                            <dt class="text-gray-500">Saison</dt>
                            <dd class="font-medium text-gray-900">{{ form.season }}</dd>

                            <dt class="text-gray-500">Début</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(form.starts_at) }}</dd>

                            <dt class="text-gray-500">Fin</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(form.ends_at) }}</dd>
                        </dl>
                        <div v-if="form.description" class="mt-3 border-t border-gray-200 pt-3">
                            <dt class="text-xs text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-700">{{ form.description }}</dd>
                        </div>
                    </div>

                    <!-- Structure -->
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Structure</h4>
                        <p class="mb-3 text-sm text-gray-700">{{ structureSummary }}</p>

                        <div class="space-y-2">
                            <div
                                v-for="(phase, index) in generatedPhases"
                                :key="index"
                                class="flex items-center gap-2 text-sm"
                            >
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full text-[10px] font-semibold"
                                    :class="phase.type === 'knockout' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                                >
                                    {{ index + 1 }}
                                </span>
                                <span class="font-medium text-gray-900">{{ phase.name }}</span>
                                <span class="text-gray-400">&mdash;</span>
                                <span class="text-gray-600">
                                    {{ phase.type === 'group' ? 'Phase de groupes' : phase.type === 'knockout' ? 'Élimination directe' : 'Aller-retour' }}
                                </span>
                                <span v-if="phase.qualify_count" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-600">
                                    Top {{ phase.qualify_count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- What happens next -->
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-emerald-700">Prochaines étapes</h4>
                        <ol class="space-y-1 text-sm text-emerald-800">
                            <li>1. Inscrivez les clubs participants</li>
                            <li v-if="form.format === 'group_knockout'">2. Lancez le tirage au sort pour répartir les clubs dans les poules</li>
                            <li>{{ form.format === 'group_knockout' ? '3' : '2' }}. Générez le calendrier des matchs</li>
                            <li v-if="form.format === 'group_knockout'">4. Le tableau final se génère automatiquement à la fin des poules</li>
                            <li v-if="form.format === 'league_playoffs'">3. Les playoffs se génèrent automatiquement à la fin de la saison régulière</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Navigation buttons -->
            <div class="mt-6 flex items-center justify-between">
                <button
                    v-if="step > 1"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    @click="prevStep"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Retour
                </button>
                <div v-else />

                <button
                    v-if="step < totalSteps"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    :disabled="(step === 1 && !canProceedStep1) || (step === 2 && !canProceedStep2) || (step === 3 && !canProceedStep3)"
                    @click="nextStep"
                >
                    Suivant
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Créer la compétition
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

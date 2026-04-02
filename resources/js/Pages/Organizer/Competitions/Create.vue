<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    organizer: Object,
});

const step = ref(1);
const totalSteps = 3;

const form = useForm({
    name: '',
    sport_type: '',
    season: '',
    format: 'league',
    description: '',
    starts_at: '',
    ends_at: '',
    phases: [],
});

const sportOptions = [
    { value: 'football', label: 'Football' },
    { value: 'basketball', label: 'Basketball' },
    { value: 'handball', label: 'Handball' },
    { value: 'volleyball', label: 'Volleyball' },
    { value: 'rugby', label: 'Rugby' },
    { value: 'natation', label: 'Natation' },
];

const formatOptions = [
    { value: 'league', label: 'Championnat' },
    { value: 'cup', label: 'Coupe' },
    { value: 'tournament', label: 'Tournoi' },
];

const phaseTypeOptions = [
    { value: 'group', label: 'Phase de groupes' },
    { value: 'knockout', label: 'Elimination directe' },
    { value: 'round_robin', label: 'Aller-retour' },
    { value: 'final', label: 'Finale' },
];

const phaseTypeLabel = (type) => {
    return phaseTypeOptions.find(o => o.value === type)?.label || type;
};

const sportLabel = (value) => {
    return sportOptions.find(o => o.value === value)?.label || value;
};

const formatLabel = (value) => {
    return formatOptions.find(o => o.value === value)?.label || value;
};

const canProceedStep1 = computed(() => {
    return form.name && form.sport_type && form.season && form.format && form.starts_at && form.ends_at;
});

const canProceedStep2 = computed(() => {
    return form.phases.length > 0 && form.phases.every(p => p.name && p.type);
});

const addPhase = () => {
    form.phases.push({ name: '', type: 'group' });
};

const removePhase = (index) => {
    form.phases.splice(index, 1);
};

const nextStep = () => {
    if (step.value < totalSteps) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submit = () => {
    form.post(route('organizer.competitions.store'));
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
    <Head title="Nouvelle competition" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Nouvelle competition</h2>
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
                                {{ s }}
                            </div>
                            <span class="hidden text-sm font-medium sm:inline" :class="s <= step ? 'text-gray-900' : 'text-gray-400'">
                                {{ s === 1 ? 'Informations' : s === 2 ? 'Phases' : 'Recapitulatif' }}
                            </span>
                        </div>
                        <div v-if="s < totalSteps" class="mx-4 h-px flex-1 bg-gray-200">
                            <div class="h-full bg-emerald-600 transition-all" :style="{ width: step > s ? '100%' : '0%' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Basic info -->
            <div v-show="step === 1" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-5 text-base font-semibold text-gray-900">Informations generales</h3>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nom de la competition *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ex : Championnat regional U17"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Sport *</label>
                            <select
                                v-model="form.sport_type"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Choisir un sport</option>
                                <option v-for="opt in sportOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.sport_type" class="mt-1 text-xs text-red-600">{{ form.errors.sport_type }}</p>
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
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Format *</label>
                        <select
                            v-model="form.format"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option v-for="opt in formatOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.format" class="mt-1 text-xs text-red-600">{{ form.errors.format }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Description optionnelle..."
                        />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Date de debut *</label>
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
                </div>
            </div>

            <!-- Step 2: Phases -->
            <div v-show="step === 2" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Phases de la competition</h3>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                        @click="addPhase"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter une phase
                    </button>
                </div>

                <div v-if="form.phases.length" class="space-y-3">
                    <div
                        v-for="(phase, index) in form.phases"
                        :key="index"
                        class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-4"
                    >
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
                            {{ index + 1 }}
                        </span>
                        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Nom de la phase</label>
                                <input
                                    v-model="phase.name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Ex : Poule A"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                                <select
                                    v-model="phase.type"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                                    <option v-for="opt in phaseTypeOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="mt-5 flex-shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                            @click="removePhase(index)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-else class="rounded-lg border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm text-gray-400">Aucune phase ajoutee. Cliquez sur "Ajouter une phase" pour commencer.</p>
                </div>

                <p v-if="form.errors.phases" class="mt-2 text-xs text-red-600">{{ form.errors.phases }}</p>
            </div>

            <!-- Step 3: Review -->
            <div v-show="step === 3" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-5 text-base font-semibold text-gray-900">Recapitulatif</h3>

                <div class="space-y-4">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Informations</h4>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <dt class="text-gray-500">Nom</dt>
                            <dd class="font-medium text-gray-900">{{ form.name }}</dd>

                            <dt class="text-gray-500">Sport</dt>
                            <dd class="font-medium text-gray-900">{{ sportLabel(form.sport_type) }}</dd>

                            <dt class="text-gray-500">Saison</dt>
                            <dd class="font-medium text-gray-900">{{ form.season }}</dd>

                            <dt class="text-gray-500">Format</dt>
                            <dd class="font-medium text-gray-900">{{ formatLabel(form.format) }}</dd>

                            <dt class="text-gray-500">Debut</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(form.starts_at) }}</dd>

                            <dt class="text-gray-500">Fin</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(form.ends_at) }}</dd>
                        </dl>
                        <div v-if="form.description" class="mt-3 border-t border-gray-200 pt-3">
                            <dt class="text-xs text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-700">{{ form.description }}</dd>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Phases ({{ form.phases.length }})
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="(phase, index) in form.phases"
                                :key="index"
                                class="flex items-center gap-2 text-sm"
                            >
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-semibold text-emerald-700">
                                    {{ index + 1 }}
                                </span>
                                <span class="font-medium text-gray-900">{{ phase.name }}</span>
                                <span class="text-gray-400">&mdash;</span>
                                <span class="text-gray-600">{{ phaseTypeLabel(phase.type) }}</span>
                            </div>
                        </div>
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
                    :disabled="(step === 1 && !canProceedStep1) || (step === 2 && !canProceedStep2)"
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
                    Creer la competition
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

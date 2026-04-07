<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    competition: Object,
});

const phaseTypeOptions = [
    { value: 'group', label: 'Phase de groupes' },
    { value: 'knockout', label: 'Elimination directe' },
    { value: 'round_robin', label: 'Aller-retour' },
    { value: 'final', label: 'Finale' },
];

const phaseTypeLabel = (type) => {
    return phaseTypeOptions.find(o => o.value === type)?.label || type;
};

const isGroupType = (type) => ['group', 'round_robin'].includes(type);

const addForm = useForm({
    name: '',
    type: 'group',
    qualify_count: 2,
});

const editForm = useForm({
    name: '',
    type: 'group',
    qualify_count: 2,
});

const showEditModal = ref(false);
const editingPhase = ref(null);
const showDeleteModal = ref(false);
const phaseToDelete = ref(null);
const showFinishModal = ref(false);
const phaseToFinish = ref(null);

const addPhase = () => {
    addForm.post(route('organizer.competitions.phases.store', props.competition.id), {
        preserveScroll: true,
        onSuccess: () => addForm.reset(),
    });
};

const openEdit = (phase) => {
    editingPhase.value = phase;
    editForm.name = phase.name;
    editForm.type = phase.type;
    editForm.qualify_count = phase.qualify_count ?? 2;
    showEditModal.value = true;
};

const updatePhase = () => {
    if (!editingPhase.value) return;
    editForm.put(route('organizer.competitions.phases.update', [props.competition.id, editingPhase.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editingPhase.value = null;
        },
    });
};

const confirmDelete = (phase) => {
    phaseToDelete.value = phase;
    showDeleteModal.value = true;
};

const deletePhase = () => {
    if (!phaseToDelete.value) return;
    router.delete(route('organizer.competitions.phases.destroy', [props.competition.id, phaseToDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            phaseToDelete.value = null;
        },
    });
};

// ─── Schedule generation config ───
const showGenerateModal = ref(false);
const phaseToGenerate = ref(null);
const generateConfig = ref({
    parallel_matches: 1,
    interval_minutes: 120,
    start_time: '09:00',
    days_between: 7,
});

const openGenerate = (phase) => {
    phaseToGenerate.value = phase;
    showGenerateModal.value = true;
};

const generateSchedule = () => {
    if (!phaseToGenerate.value) return;
    router.post(
        route('organizer.competitions.phases.generate-schedule', [props.competition.id, phaseToGenerate.value.id]),
        { ...generateConfig.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                showGenerateModal.value = false;
                phaseToGenerate.value = null;
            },
        },
    );
};

const confirmFinish = (phase) => {
    phaseToFinish.value = phase;
    showFinishModal.value = true;
};

const finishPhase = () => {
    if (!phaseToFinish.value) return;
    router.put(route('organizer.competitions.phases.update', [props.competition.id, phaseToFinish.value.id]), {
        name: phaseToFinish.value.name,
        type: phaseToFinish.value.type,
        status: 'finished',
        qualify_count: phaseToFinish.value.qualify_count ?? 2,
    }, {
        preserveScroll: true,
        onFinish: () => {
            showFinishModal.value = false;
            phaseToFinish.value = null;
        },
    });
};

const hasKnockoutPhase = () => {
    return props.competition.phases?.some(p => p.type === 'knockout');
};
</script>

<template>
    <Head :title="`Phases — ${competition.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
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
                        <h2 class="text-lg font-semibold text-gray-900">Phases</h2>
                        <p class="text-xs text-gray-500">{{ competition.name }}</p>
                    </div>
                </div>

                <!-- Bracket link -->
                <Link
                    v-if="hasKnockoutPhase()"
                    :href="route('organizer.competitions.bracket', competition.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
                    </svg>
                    Voir le tableau
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Add phase form -->
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Ajouter une phase</h3>
                <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="addPhase">
                    <div class="flex-1">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nom de la phase</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Ex : Poule A"
                        />
                        <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                    </div>
                    <div class="sm:w-48">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Type</label>
                        <select
                            v-model="addForm.type"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option v-for="opt in phaseTypeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="addForm.errors.type" class="mt-1 text-xs text-red-600">{{ addForm.errors.type }}</p>
                    </div>
                    <div v-if="isGroupType(addForm.type)" class="sm:w-32">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Qualifiés/poule</label>
                        <input
                            v-model.number="addForm.qualify_count"
                            type="number"
                            min="1"
                            max="10"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <p v-if="addForm.errors.qualify_count" class="mt-1 text-xs text-red-600">{{ addForm.errors.qualify_count }}</p>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="addForm.processing || !addForm.name"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter
                    </button>
                </form>
            </div>

            <!-- Phases list -->
            <div v-if="competition.phases?.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="phase in competition.phases"
                    :key="phase.id"
                    class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ phase.name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ phaseTypeLabel(phase.type) }}</p>
                        </div>
                        <CompetitionBadge :status="phase.status || 'draft'" />
                    </div>

                    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                        <span>{{ phase.games_count ?? phase.games?.length ?? 0 }} match(s)</span>
                        <span v-if="isGroupType(phase.type)" class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {{ phase.qualify_count ?? 2 }} qualifié(s)/poule
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100"
                            @click="openEdit(phase)"
                        >
                            Modifier
                        </button>
                        <Link
                            v-if="(phase.games_count ?? 0) > 0"
                            :href="route('organizer.competitions.matches.index', competition.id)"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-blue-600 transition hover:bg-blue-50"
                        >
                            Voir les matchs
                        </Link>
                        <button
                            v-if="phase.status !== 'finished'"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                            @click="openGenerate(phase)"
                        >
                            Générer calendrier
                        </button>
                        <button
                            v-if="phase.status === 'ongoing' && isGroupType(phase.type)"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-50"
                            @click="confirmFinish(phase)"
                        >
                            Terminer la phase
                        </button>
                        <button
                            class="ml-auto rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50"
                            @click="confirmDelete(phase)"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">Aucune phase. Ajoutez la premiere phase ci-dessus.</p>
            </div>
        </div>

        <!-- Edit modal -->
        <Modal :show="showEditModal" max-width="md" @close="showEditModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Modifier la phase</h3>
                <form class="space-y-4" @submit.prevent="updatePhase">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nom</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                        <select
                            v-model="editForm.type"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option v-for="opt in phaseTypeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="editForm.errors.type" class="mt-1 text-xs text-red-600">{{ editForm.errors.type }}</p>
                    </div>
                    <div v-if="isGroupType(editForm.type)">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre de qualifiés par poule</label>
                        <input
                            v-model.number="editForm.qualify_count"
                            type="number"
                            min="1"
                            max="10"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <p class="mt-1 text-xs text-gray-400">Les X premiers de chaque poule seront qualifiés pour la phase suivante.</p>
                        <p v-if="editForm.errors.qualify_count" class="mt-1 text-xs text-red-600">{{ editForm.errors.qualify_count }}</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            @click="showEditModal = false"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="editForm.processing || !editForm.name"
                        >
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" max-width="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Supprimer la phase</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Etes-vous sur de vouloir supprimer
                    <strong>{{ phaseToDelete?.name }}</strong> ?
                    Tous les matchs associes seront egalement supprimes.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Annuler
                    </button>
                    <button
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                        @click="deletePhase"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Finish phase confirmation modal -->
        <Modal :show="showFinishModal" max-width="2xl" @close="showFinishModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Terminer la phase</h3>
                        <p class="text-sm text-gray-500">{{ phaseToFinish?.name }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-700">
                        En terminant cette phase, les
                        <strong class="text-emerald-600">{{ phaseToFinish?.qualify_count ?? 2 }} premier(s)</strong>
                        de chaque poule seront automatiquement qualifiés pour la phase finale.
                    </p>
                    <p class="mt-2 text-xs text-gray-500">
                        Le tableau du tournoi (bracket) sera généré automatiquement.
                        Cette action est irréversible.
                    </p>
                </div>

                <!-- Standings preview -->
                <div v-if="phaseToFinish?.standings?.length" class="mt-4">
                    <p class="mb-2 text-xs font-medium text-gray-500">Classement actuel :</p>
                    <div class="space-y-1">
                        <div
                            v-for="(standing, index) in phaseToFinish.standings"
                            :key="standing.id"
                            class="flex items-center justify-between rounded-md px-3 py-1.5 text-xs"
                            :class="index < (phaseToFinish.qualify_count ?? 2) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'bg-gray-50 text-gray-500'"
                        >
                            <span>{{ index + 1 }}. {{ standing.club?.name }}</span>
                            <span>{{ standing.points }} pts</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        @click="showFinishModal = false"
                    >
                        Annuler
                    </button>
                    <button
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
                        @click="finishPhase"
                    >
                        Terminer et qualifier
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Generate schedule config modal -->
        <Modal :show="showGenerateModal" max-width="md" @close="showGenerateModal = false">
            <div class="p-6">
                <h3 class="mb-1 text-lg font-semibold text-gray-900">Générer le calendrier</h3>
                <p class="mb-5 text-sm text-gray-500">{{ phaseToGenerate?.name }} — Configurez la planification des matchs.</p>

                <div class="space-y-5">
                    <!-- Parallel matches -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Matchs en parallèle</label>
                        <div class="flex items-center gap-3">
                            <input
                                v-model.number="generateConfig.parallel_matches"
                                type="number"
                                min="1"
                                max="10"
                                class="w-24 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <span class="text-xs text-gray-500">terrain(s) disponible(s) en même temps</span>
                        </div>
                    </div>

                    <!-- Interval -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Intervalle entre les matchs</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="mins in [60, 90, 120, 180]"
                                :key="mins"
                                type="button"
                                class="rounded-lg border-2 px-3 py-2 text-center text-sm transition"
                                :class="generateConfig.interval_minutes === mins
                                    ? 'border-emerald-500 bg-emerald-50 font-semibold text-emerald-700'
                                    : 'border-gray-100 text-gray-700 hover:border-gray-300'"
                                @click="generateConfig.interval_minutes = mins"
                            >
                                {{ mins >= 60 ? `${mins / 60}h` : `${mins}min` }}{{ mins === 90 ? '30' : '' }}
                            </button>
                        </div>
                    </div>

                    <!-- Start time -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Heure du premier match</label>
                        <input
                            v-model="generateConfig.start_time"
                            type="time"
                            class="w-32 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>

                    <!-- Days between -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fréquence des journées</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="opt in [{ days: 1, label: 'Chaque jour' }, { days: 7, label: 'Chaque semaine' }, { days: 14, label: 'Toutes les 2 sem.' }]"
                                :key="opt.days"
                                type="button"
                                class="rounded-lg border-2 px-3 py-2 text-center text-sm transition"
                                :class="generateConfig.days_between === opt.days
                                    ? 'border-emerald-500 bg-emerald-50 font-semibold text-emerald-700'
                                    : 'border-gray-100 text-gray-700 hover:border-gray-300'"
                                @click="generateConfig.days_between = opt.days"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="rounded-lg bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Aperçu</p>
                        <p class="text-sm text-gray-700">
                            <strong>{{ generateConfig.parallel_matches }}</strong> match(s) en même temps,
                            toutes les <strong>{{ generateConfig.interval_minutes >= 60 ? `${generateConfig.interval_minutes / 60}h` : `${generateConfig.interval_minutes}min` }}</strong>,
                            à partir de <strong>{{ generateConfig.start_time }}</strong>,
                            {{ generateConfig.days_between === 1 ? 'chaque jour' : generateConfig.days_between === 7 ? 'chaque semaine' : `tous les ${generateConfig.days_between} jours` }}.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        @click="showGenerateModal = false"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="generateSchedule"
                    >
                        Générer
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

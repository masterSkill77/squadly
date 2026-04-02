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

const addForm = useForm({
    name: '',
    type: 'group',
});

const editForm = useForm({
    name: '',
    type: 'group',
});

const showEditModal = ref(false);
const editingPhase = ref(null);
const showDeleteModal = ref(false);
const phaseToDelete = ref(null);

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

const generateSchedule = (phase) => {
    router.post(route('organizer.competitions.phases.generate-schedule', [props.competition.id, phase.id]), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Phases — ${competition.name}`" />

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
                    <h2 class="text-lg font-semibold text-gray-900">Phases</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
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
                        <span>{{ phase.games?.length || 0 }} match(s)</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-50 pt-4">
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100"
                            @click="openEdit(phase)"
                        >
                            Modifier
                        </button>
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                            @click="generateSchedule(phase)"
                        >
                            Generer calendrier
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
    </AuthenticatedLayout>
</template>

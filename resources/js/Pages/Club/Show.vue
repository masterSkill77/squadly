<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ClubInfoCard from '@/Components/Club/ClubInfoCard.vue';
import SectionCard from '@/Components/Club/SectionCard.vue';
import AddSectionModal from '@/Components/Club/AddSectionModal.vue';
import AddTeamModal from '@/Components/Club/AddTeamModal.vue';
import EditTeamModal from '@/Components/Club/EditTeamModal.vue';
import DeleteConfirmModal from '@/Components/Club/DeleteConfirmModal.vue';

const props = defineProps({
    club: Object,
    sections: Array,
    availableSports: Array,
});

const showAddSection = ref(false);
const showAddTeam = ref(false);
const showEditTeam = ref(false);
const showDelete = ref(false);

const selectedSection = ref(null);
const selectedTeam = ref(null);
const deleteTarget = ref(null);
const deleting = ref(false);

function onAddTeam(section) {
    selectedSection.value = section;
    showAddTeam.value = true;
}

function onEditTeam(team) {
    selectedTeam.value = team;
    showEditTeam.value = true;
}

function onDeleteTeam(team) {
    deleteTarget.value = { type: 'team', id: team.id, name: team.name };
    showDelete.value = true;
}

function onDeleteSection(section) {
    deleteTarget.value = { type: 'section', id: section.id, name: section.sport_type };
    showDelete.value = true;
}

function confirmDelete() {
    deleting.value = true;
    const route_ = deleteTarget.value.type === 'team'
        ? route('teams.destroy', deleteTarget.value.id)
        : route('sections.destroy', deleteTarget.value.id);

    router.delete(route_, {
        onFinish: () => { deleting.value = false; showDelete.value = false; },
    });
}
</script>

<template>
    <Head title="Mon club" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Mon club</h2>
        </template>

        <div class="space-y-6">
            <ClubInfoCard :club="club" />

            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Sections & équipes</h3>
                <button
                    v-if="availableSports.length"
                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="showAddSection = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Ajouter un sport
                </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <SectionCard
                    v-for="section in sections"
                    :key="section.id"
                    :section="section"
                    @add-team="onAddTeam"
                    @edit-team="onEditTeam"
                    @delete-team="onDeleteTeam"
                    @delete-section="onDeleteSection"
                />
            </div>

            <div v-if="!sections.length" class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                <p class="text-sm text-gray-400">Aucune section. Ajoutez un sport pour commencer.</p>
            </div>
        </div>

        <AddSectionModal :show="showAddSection" :available-sports="availableSports" @close="showAddSection = false" />
        <AddTeamModal :show="showAddTeam" :section="selectedSection" @close="showAddTeam = false" />
        <EditTeamModal :show="showEditTeam" :team="selectedTeam" @close="showEditTeam = false" />
        <DeleteConfirmModal
            :show="showDelete"
            :processing="deleting"
            :message="deleteTarget?.type === 'section'
                ? 'Cette section et toutes ses équipes seront supprimées.'
                : `L'équipe « ${deleteTarget?.name} » sera supprimée.`"
            @close="showDelete = false"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

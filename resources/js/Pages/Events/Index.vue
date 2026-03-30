<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventList from '@/Components/Events/EventList.vue';
import EventModal from '@/Components/Events/EventModal.vue';

const props = defineProps({ events: Array, teams: Array, sections: Array, eventTypes: Array });

const showModal = ref(false);
const editingEvent = ref(null);

function openCreate() {
    editingEvent.value = null;
    showModal.value = true;
}

function openEdit(event) {
    editingEvent.value = event;
    showModal.value = true;
}
</script>

<template>
    <Head title="Événements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Événements</h2>
        </template>

        <EventList :events="events" :teams="teams" attendance-route-name="attendance.show" convocation-route-name="convocations.show" @create="openCreate" @edit="openEdit" />

        <EventModal
            :show="showModal"
            :teams="teams"
            :sections="sections"
            :event-types="eventTypes"
            :event="editingEvent"
            @close="showModal = false"
        />
    </AuthenticatedLayout>
</template>

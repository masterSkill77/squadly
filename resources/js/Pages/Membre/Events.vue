<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventList from '@/Components/Events/EventList.vue';

const props = defineProps({ events: Array, teams: Array });
</script>

<template>
    <Head title="Événements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Événements</h2>
        </template>

        <EventList :events="events" :teams="teams" />
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AttendanceStats from '@/Components/Attendance/AttendanceStats.vue';

const props = defineProps({ players: Array, teams: Array });
</script>

<template>
    <Head title="Présences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Présences</h2>
        </template>

        <AttendanceStats :players="players" :teams="teams" player-route-name="members.show" />
    </AuthenticatedLayout>
</template>

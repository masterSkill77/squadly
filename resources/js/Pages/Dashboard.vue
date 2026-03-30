<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDashboard from '@/Components/Dashboard/AdminDashboard.vue';
import CoachDashboard from '@/Components/Dashboard/CoachDashboard.vue';
import MembreDashboard from '@/Components/Dashboard/MembreDashboard.vue';
import { Head } from '@inertiajs/vue3';
import { Role, roleLabels } from '@/Utils/roles';

const props = defineProps({
    role: String,
    permissions: Array,
    hasCompletedOnboarding: Boolean,
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">
                Tableau de bord <sup class="text-xs font-medium text-emerald-600">{{ roleLabels[role] || role }}</sup>
            </h2>
        </template>

        <AdminDashboard v-if="role === Role.Admin" />
        <CoachDashboard v-else-if="role === Role.Coach" />
        <MembreDashboard v-else />
    </AuthenticatedLayout>
</template>

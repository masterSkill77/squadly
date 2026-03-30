<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Tactique" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Tactique</h2>
        </template>

        <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" /></svg>
            <h3 class="mt-4 text-base font-semibold text-gray-900">Espace Tactique</h3>
            <p class="mt-2 text-sm text-gray-500">Préparez vos compositions, schémas tactiques et plans de jeu pour chaque match.</p>
            <p class="mt-1 text-xs text-gray-400">Bientôt disponible.</p>
        </div>
    </AuthenticatedLayout>
</template>

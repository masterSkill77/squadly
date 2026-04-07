<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BracketTree from '@/Components/Competition/BracketTree.vue';

const props = defineProps({
    competition: Object,
    bracket: Object,
    phase: Object,
});
</script>

<template>
    <Head :title="`Tableau — ${competition.name}`" />

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
                    <h2 class="text-lg font-semibold text-gray-900">Tableau du tournoi</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Phase info -->
            <div v-if="phase" class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white px-5 py-3 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 10.875h-2.25A3.375 3.375 0 0 0 7.5 14.25v4.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ phase.name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ bracket.totalRounds }} tour(s) &middot;
                        {{ bracket.rounds.reduce((sum, r) => sum + r.games.length, 0) }} match(s)
                    </p>
                </div>
            </div>

            <!-- Bracket -->
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <BracketTree
                    :rounds="bracket.rounds"
                    :total-rounds="bracket.totalRounds"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

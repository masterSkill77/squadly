<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import BracketTree from '@/Components/Competition/BracketTree.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

const props = defineProps({
    competition: Object,
    bracket: Object,
    phase: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head :title="`Tableau — ${competition.name}`" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <div class="mx-auto max-w-7xl px-4 pt-28 pb-8 sm:px-6">
            <!-- Header -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('competitions.show', competition.id)"
                        class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900">Tableau du tournoi</h1>
                            <CompetitionBadge :status="competition.status" />
                        </div>
                        <p class="mt-0.5 text-sm text-gray-500">{{ competition.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Bracket -->
            <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
                <BracketTree
                    :rounds="bracket.rounds"
                    :total-rounds="bracket.totalRounds"
                />
            </div>
        </div>
    </div>
</template>

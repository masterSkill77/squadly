<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import StandingsTable from '@/Components/Competition/StandingsTable.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

const props = defineProps({
    competition: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const phases = computed(() => props.competition.phases ?? []);
</script>

<template>
    <Head :title="`Classement - ${competition.name}`" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <!-- Content -->
        <div class="mx-auto max-w-5xl px-4 pt-28 pb-8 sm:px-6">
            <!-- Header -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ competition.name }}</h1>
                    <CompetitionBadge :status="competition.status" />
                </div>
                <p class="mt-1 text-sm text-gray-500">Classement officiel</p>
            </div>

            <!-- Standings per phase -->
            <div class="mt-6 space-y-8">
                <template v-if="phases.length">
                    <div v-for="phase in phases" :key="phase.id">
                        <h3 class="mb-3 text-base font-semibold text-gray-900">{{ phase.name }}</h3>
                        <StandingsTable :standings="phase.standings ?? []" :qualify-count="phase.qualify_count ?? 0" />
                    </div>
                </template>
                <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-sm text-gray-400">Aucun classement disponible pour cette competition.</p>
                </div>
            </div>
        </div>
    </div>
</template>

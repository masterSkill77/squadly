<script setup>
import MatchCard from './MatchCard.vue';

defineProps({
    phases: { type: Array, required: true },
});
</script>

<template>
    <div class="space-y-8">
        <div v-for="phase in phases" :key="phase.id || phase.name">
            <!-- Phase header -->
            <div class="mb-3 flex items-center gap-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ phase.name }}</h3>
                <div class="h-px flex-1 bg-gray-100"></div>
                <span class="text-xs text-gray-400">{{ phase.games?.length || 0 }} match{{ (phase.games?.length || 0) > 1 ? 's' : '' }}</span>
            </div>

            <!-- Games grid -->
            <div v-if="phase.games?.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <MatchCard
                    v-for="game in phase.games"
                    :key="game.id"
                    :game="game"
                />
            </div>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-6 text-center">
                <p class="text-sm text-gray-400">Aucun match pour cette phase.</p>
            </div>
        </div>

        <div v-if="!phases.length" class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
            <p class="text-sm text-gray-400">Aucune phase programmée.</p>
        </div>
    </div>
</template>

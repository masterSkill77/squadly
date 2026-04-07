<script setup>
import { computed } from 'vue';
import BracketNode from './BracketNode.vue';

const props = defineProps({
    rounds: { type: Array, required: true },
    totalRounds: { type: Number, required: true },
});

const tree = computed(() => {
    if (!props.rounds.length || !props.totalRounds) return null;

    // Build lookup: "round-position" → game
    const gameMap = {};
    const roundLabels = {};
    for (const round of props.rounds) {
        roundLabels[round.round] = round.label;
        for (const game of round.games) {
            gameMap[`${round.round}-${game.bracket_position}`] = game;
        }
    }

    // Recursive tree builder: final (round 1) → first round (highest round number)
    function buildNode(round, position) {
        const game = gameMap[`${round}-${position}`];
        if (!game) return null;

        if (round >= props.totalRounds) {
            return { game, children: [], label: roundLabels[round] };
        }

        return {
            game,
            label: roundLabels[round],
            children: [
                buildNode(round + 1, position * 2 - 1),
                buildNode(round + 1, position * 2),
            ].filter(Boolean),
        };
    }

    return buildNode(1, 1);
});

// Round labels in display order (first round → final)
const orderedLabels = computed(() => {
    return [...props.rounds].reverse().map(r => r.label);
});
</script>

<template>
    <div v-if="tree" class="space-y-4">
        <!-- Round labels -->
        <div class="flex gap-2 overflow-x-auto pb-2">
            <span
                v-for="(label, i) in orderedLabels"
                :key="i"
                class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                :class="i === orderedLabels.length - 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
            >
                {{ label }}
            </span>
        </div>

        <!-- Bracket tree -->
        <div class="overflow-x-auto pb-4">
            <BracketNode :node="tree" />
        </div>
    </div>

    <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </svg>
        <p class="mt-3 text-sm text-gray-400">Aucun tableau disponible.</p>
        <p class="mt-1 text-xs text-gray-400">Le tableau sera généré automatiquement à la fin des phases de poules.</p>
    </div>
</template>

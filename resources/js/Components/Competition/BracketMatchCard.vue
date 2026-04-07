<script setup>
import { computed } from 'vue';

const props = defineProps({
    game: { type: Object, required: true },
});

const isFinished = computed(() => props.game.status === 'finished');
const isPending = computed(() => !props.game.home_club && !props.game.away_club);

const homeWon = computed(() => isFinished.value && props.game.winner_id === props.game.home_club?.id);
const awayWon = computed(() => isFinished.value && props.game.winner_id === props.game.away_club?.id);

const formattedDate = computed(() => {
    if (!props.game.scheduled_at) return '';
    const d = new Date(props.game.scheduled_at);
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
});
</script>

<template>
    <div class="w-52 rounded-lg border bg-white shadow-sm" :class="isFinished ? 'border-gray-200' : 'border-gray-100'">
        <!-- Home team -->
        <div
            class="flex items-center justify-between border-b px-3 py-2"
            :class="[
                homeWon ? 'bg-emerald-50 border-emerald-100' : 'border-gray-100',
                !game.home_club ? 'opacity-40' : '',
            ]"
        >
            <span
                class="truncate text-xs font-medium"
                :class="homeWon ? 'text-emerald-700 font-bold' : 'text-gray-700'"
            >
                {{ game.home_club?.name || 'A déterminer' }}
            </span>
            <span
                v-if="isFinished"
                class="ml-2 min-w-[1.25rem] text-center text-xs font-bold"
                :class="homeWon ? 'text-emerald-700' : 'text-gray-400'"
            >
                {{ game.home_score }}
            </span>
        </div>

        <!-- Away team -->
        <div
            class="flex items-center justify-between px-3 py-2"
            :class="[
                awayWon ? 'bg-emerald-50' : '',
                !game.away_club ? 'opacity-40' : '',
            ]"
        >
            <span
                class="truncate text-xs font-medium"
                :class="awayWon ? 'text-emerald-700 font-bold' : 'text-gray-700'"
            >
                {{ game.away_club?.name || 'A déterminer' }}
            </span>
            <span
                v-if="isFinished"
                class="ml-2 min-w-[1.25rem] text-center text-xs font-bold"
                :class="awayWon ? 'text-emerald-700' : 'text-gray-400'"
            >
                {{ game.away_score }}
            </span>
        </div>

        <!-- Date -->
        <div v-if="formattedDate && !isPending" class="border-t border-gray-50 px-3 py-1 text-center">
            <span class="text-[10px] text-gray-400">{{ formattedDate }}</span>
        </div>
    </div>
</template>

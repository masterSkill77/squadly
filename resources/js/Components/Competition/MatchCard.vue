<script setup>
import { computed } from 'vue';
import CompetitionBadge from './CompetitionBadge.vue';

const props = defineProps({
    game: { type: Object, required: true },
});

const statusMap = {
    scheduled: { label: 'Programmé', classes: 'bg-blue-50 text-blue-700' },
    ongoing: { label: 'En cours', classes: 'bg-emerald-50 text-emerald-700' },
    finished: { label: 'Terminé', classes: 'bg-gray-100 text-gray-600' },
    cancelled: { label: 'Annulé', classes: 'bg-red-50 text-red-600' },
    postponed: { label: 'Reporté', classes: 'bg-amber-50 text-amber-700' },
};

const statusBadge = computed(() => statusMap[props.game.status] || statusMap.scheduled);

const formattedDate = computed(() => {
    if (!props.game.scheduled_at) return '';
    const d = new Date(props.game.scheduled_at);
    return d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
});

const formattedTime = computed(() => {
    if (!props.game.scheduled_at) return '';
    const d = new Date(props.game.scheduled_at);
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
});

const isFinished = computed(() => props.game.status === 'finished');
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-4 transition hover:shadow-md">
        <!-- Header: date + status -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <span>{{ formattedDate }} &middot; {{ formattedTime }}</span>
            </div>
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="statusBadge.classes">
                {{ statusBadge.label }}
            </span>
        </div>

        <!-- Score / clubs -->
        <div class="mt-4 flex items-center justify-between gap-3">
            <!-- Home -->
            <div class="flex-1 text-right">
                <p class="text-sm font-semibold text-gray-900">{{ game.home_club?.name ?? game.homeClub?.name ?? game.homeClub }}</p>
            </div>

            <!-- Score -->
            <div class="flex items-center gap-2">
                <template v-if="isFinished">
                    <span class="rounded-lg bg-gray-900 px-3 py-1.5 text-lg font-bold text-white">{{ game.home_score }}</span>
                    <span class="text-xs font-medium text-gray-400">-</span>
                    <span class="rounded-lg bg-gray-900 px-3 py-1.5 text-lg font-bold text-white">{{ game.away_score }}</span>
                </template>
                <template v-else>
                    <span class="text-sm font-medium text-gray-400">vs</span>
                </template>
            </div>

            <!-- Away -->
            <div class="flex-1 text-left">
                <p class="text-sm font-semibold text-gray-900">{{ game.away_club?.name ?? game.awayClub?.name ?? game.awayClub }}</p>
            </div>
        </div>

        <!-- Location -->
        <div v-if="game.location" class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <span>{{ game.location }}</span>
        </div>
    </div>
</template>

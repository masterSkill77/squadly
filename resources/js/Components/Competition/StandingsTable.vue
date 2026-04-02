<script setup>
import { computed } from 'vue';

const props = defineProps({
    standings: { type: Array, required: true },
});

const sorted = computed(() =>
    [...props.standings].sort((a, b) => {
        if (b.points !== a.points) return b.points - a.points;
        const diffA = a.goals_for - a.goals_against;
        const diffB = b.goals_for - b.goals_against;
        if (diffB !== diffA) return diffB - diffA;
        return b.goals_for - a.goals_for;
    }),
);

function diff(row) {
    const d = row.goals_for - row.goals_against;
    return d > 0 ? `+${d}` : `${d}`;
}
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-gray-100 bg-white">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-emerald-600 text-white">
                    <th class="px-3 py-3 text-left font-semibold">#</th>
                    <th class="px-3 py-3 text-left font-semibold">Club</th>
                    <th class="px-3 py-3 text-center font-semibold">J</th>
                    <th class="px-3 py-3 text-center font-semibold">V</th>
                    <th class="px-3 py-3 text-center font-semibold">N</th>
                    <th class="px-3 py-3 text-center font-semibold">D</th>
                    <th class="px-3 py-3 text-center font-semibold">BP</th>
                    <th class="px-3 py-3 text-center font-semibold">BC</th>
                    <th class="px-3 py-3 text-center font-semibold">Diff</th>
                    <th class="px-3 py-3 text-center font-semibold">Pts</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in sorted"
                    :key="row.club"
                    class="border-t border-gray-50 transition hover:bg-emerald-50/50"
                >
                    <td class="px-3 py-2.5 font-medium text-gray-500">{{ index + 1 }}</td>
                    <td class="px-3 py-2.5 font-semibold text-gray-900">{{ row.club?.name ?? row.club }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.played }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.won }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.drawn }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.lost }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.goals_for }}</td>
                    <td class="px-3 py-2.5 text-center text-gray-700">{{ row.goals_against }}</td>
                    <td class="px-3 py-2.5 text-center font-medium" :class="row.goals_for - row.goals_against > 0 ? 'text-emerald-600' : row.goals_for - row.goals_against < 0 ? 'text-red-500' : 'text-gray-500'">
                        {{ diff(row) }}
                    </td>
                    <td class="px-3 py-2.5 text-center font-bold text-gray-900">{{ row.points }}</td>
                </tr>
                <tr v-if="!standings.length">
                    <td colspan="10" class="px-3 py-8 text-center text-sm text-gray-400">Aucune donnée de classement.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

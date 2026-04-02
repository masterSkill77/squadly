<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    game: { type: Object, required: true },
    players: { type: Array, required: true },
});

const emit = defineEmits(['submit']);

const form = useForm({
    stats: props.players.map(p => ({
        user_id: p.id,
        goals: p.goals ?? 0,
        assists: p.assists ?? 0,
        yellow_cards: p.yellow_cards ?? 0,
        red_cards: p.red_cards ?? 0,
        minutes_played: p.minutes_played ?? 0,
    })),
});

function submit() {
    emit('submit', form.stats);
}
</script>

<template>
    <form class="rounded-xl border border-gray-100 bg-white" @submit.prevent="submit">
        <div class="border-b border-gray-50 px-5 py-4">
            <h4 class="text-sm font-semibold text-gray-900">Statistiques des joueurs</h4>
            <p class="mt-0.5 text-xs text-gray-400">{{ game.homeClub }} vs {{ game.awayClub }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Joueur</th>
                        <th class="px-2 py-2.5 text-center text-xs font-semibold text-gray-500">Buts</th>
                        <th class="px-2 py-2.5 text-center text-xs font-semibold text-gray-500">Passes D.</th>
                        <th class="px-2 py-2.5 text-center text-xs font-semibold text-gray-500">
                            <span class="inline-block h-3 w-3 rounded-sm bg-yellow-400" title="Cartons jaunes"></span>
                        </th>
                        <th class="px-2 py-2.5 text-center text-xs font-semibold text-gray-500">
                            <span class="inline-block h-3 w-3 rounded-sm bg-red-500" title="Cartons rouges"></span>
                        </th>
                        <th class="px-2 py-2.5 text-center text-xs font-semibold text-gray-500">Min.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(player, i) in players"
                        :key="player.id"
                        class="border-t border-gray-50 transition hover:bg-gray-50/50"
                    >
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">
                                    {{ player.first_name?.charAt(0) }}{{ player.last_name?.charAt(0) }}
                                </div>
                                <span class="truncate text-sm font-medium text-gray-900">{{ player.first_name }} {{ player.last_name }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-2">
                            <input
                                v-model.number="form.stats[i].goals"
                                type="number"
                                min="0"
                                class="w-14 rounded-md border-gray-300 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </td>
                        <td class="px-2 py-2">
                            <input
                                v-model.number="form.stats[i].assists"
                                type="number"
                                min="0"
                                class="w-14 rounded-md border-gray-300 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </td>
                        <td class="px-2 py-2">
                            <input
                                v-model.number="form.stats[i].yellow_cards"
                                type="number"
                                min="0"
                                class="w-14 rounded-md border-gray-300 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </td>
                        <td class="px-2 py-2">
                            <input
                                v-model.number="form.stats[i].red_cards"
                                type="number"
                                min="0"
                                class="w-14 rounded-md border-gray-300 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </td>
                        <td class="px-2 py-2">
                            <input
                                v-model.number="form.stats[i].minutes_played"
                                type="number"
                                min="0"
                                max="120"
                                class="w-16 rounded-md border-gray-300 text-center text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </td>
                    </tr>
                    <tr v-if="!players.length">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Aucun joueur.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="players.length" class="flex justify-end border-t border-gray-50 px-5 py-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
            >
                Enregistrer les statistiques
            </button>
        </div>
    </form>
</template>

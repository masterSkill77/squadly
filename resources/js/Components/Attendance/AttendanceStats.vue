<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    players: Array,
    teams: Array,
    playerRouteName: { type: String, default: null },
});
</script>

<template>
    <div>
        <div v-if="players.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-50 bg-gray-50/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-400">Joueur</th>
                        <th class="hidden px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-400 sm:table-cell">Présent</th>
                        <th class="hidden px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-400 sm:table-cell">Absent</th>
                        <th class="hidden px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-400 sm:table-cell">Justifié</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-400">Taux</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="p in players" :key="p.user_id" class="transition hover:bg-gray-50/50">
                        <td class="px-4 py-3">
                            <component
                                :is="playerRouteName && p.link_id ? Link : 'span'"
                                :href="playerRouteName && p.link_id ? route(playerRouteName, p.link_id) : undefined"
                                class="flex items-center gap-3"
                                :class="playerRouteName && p.link_id ? 'hover:text-emerald-600 transition' : ''"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                                    {{ p.first_name.charAt(0) }}{{ p.last_name.charAt(0) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ p.first_name }} {{ p.last_name }}</span>
                            </component>
                        </td>
                        <td class="hidden px-4 py-3 text-center text-emerald-600 sm:table-cell">{{ p.present }}</td>
                        <td class="hidden px-4 py-3 text-center text-red-600 sm:table-cell">{{ p.absent }}</td>
                        <td class="hidden px-4 py-3 text-center text-amber-600 sm:table-cell">{{ p.justified }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <div class="hidden h-2 w-20 overflow-hidden rounded-full bg-gray-100 sm:block">
                                    <div class="h-full rounded-full transition-all" :class="p.rate >= 75 ? 'bg-emerald-500' : p.rate >= 50 ? 'bg-amber-500' : 'bg-red-500'" :style="{ width: p.rate + '%' }" />
                                </div>
                                <span class="text-xs font-semibold" :class="p.rate >= 75 ? 'text-emerald-600' : p.rate >= 50 ? 'text-amber-600' : 'text-red-600'">{{ p.rate }}%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <p class="text-sm text-gray-400">Aucune donnée de présence enregistrée.</p>
        </div>
    </div>
</template>

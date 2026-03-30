<script setup>
import { usePage } from '@inertiajs/vue3';
import { sportLabels } from '@/Utils/sportLabels';

const { myTeams, club, nextEvent } = usePage().props;
</script>

<template>
    <div class="space-y-8">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ club?.name || 'Mon espace' }}</h3>
            <p class="text-sm text-gray-500">Vos prochains événements et convocations.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-100 bg-white p-6">
                <h4 class="text-sm font-semibold text-gray-900">Prochain événement</h4>
                <template v-if="nextEvent">
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ nextEvent.title }}</p>
                    <p class="text-xs text-gray-500">{{ nextEvent.team_name }} · {{ new Date(nextEvent.start_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</p>
                    <span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">{{ nextEvent.type_label }}</span>
                </template>
                <p v-else class="mt-2 text-sm text-gray-400">Aucun événement à venir.</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-6">
                <h4 class="text-sm font-semibold text-gray-900">Mes équipes</h4>
                <div v-if="myTeams?.length" class="mt-3 space-y-2">
                    <div v-for="t in myTeams" :key="t.id" class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                        <span class="text-sm text-gray-700">{{ t.name }}</span>
                        <span class="text-xs text-gray-400">{{ sportLabels[t.sport_type] || t.sport_type }}</span>
                    </div>
                </div>
                <p v-else class="mt-2 text-sm text-gray-400">Vous n'êtes dans aucune équipe.</p>
            </div>
        </div>
    </div>
</template>

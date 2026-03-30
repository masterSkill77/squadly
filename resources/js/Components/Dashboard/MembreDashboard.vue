<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import { sportLabels } from '@/Utils/sportLabels';

const { myTeams, club, nextEvent, pendingConvocations } = usePage().props;

function respond(id, status) {
    router.put(route('membre.convocations.respond', id), { status }, { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ club?.name || 'Mon espace' }}</h3>
            <p class="text-sm text-gray-500">Vos prochains événements et convocations.</p>
        </div>

        <!-- Pending convocations -->
        <div v-if="pendingConvocations?.length" class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-base font-semibold text-gray-900">Convocations en attente</h4>
                <Link :href="route('membre.convocations')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Voir tout</Link>
            </div>
            <div
                v-for="c in pendingConvocations"
                :key="c.id"
                class="flex flex-col gap-3 rounded-xl border border-amber-100 bg-amber-50/30 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ c.event_title }}</p>
                    <p class="text-xs text-gray-500">{{ c.team_name }} · {{ new Date(c.event_start_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</p>
                </div>
                <div class="flex gap-2">
                    <button
                        class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                        @click="respond(c.id, 'confirmed')"
                    >
                        Confirmer
                    </button>
                    <button
                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                        @click="respond(c.id, 'declined')"
                    >
                        Décliner
                    </button>
                </div>
            </div>
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

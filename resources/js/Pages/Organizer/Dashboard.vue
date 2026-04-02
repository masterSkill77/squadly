<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

defineProps({
    organizer: Object,
    competitions: Array,
    activeCount: Number,
    pendingRegistrations: Array,
    upcomingGames: Array,
});

const totalClubs = (competitions) => {
    return competitions.reduce((sum, c) => sum + (c.clubs_count || 0), 0);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Dashboard Organisateur" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Dashboard Organisateur</h2>
        </template>

        <div class="space-y-6">
            <!-- Stat cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 10.875h-2.25A3.375 3.375 0 0 0 7.5 14.25v4.5m9 0H7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ activeCount }}</p>
                            <p class="text-xs text-gray-500">Compétitions actives</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ totalClubs(competitions) }}</p>
                            <p class="text-xs text-gray-500">Clubs inscrits (total)</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ pendingRegistrations.length }}</p>
                            <p class="text-xs text-gray-500">Inscriptions en attente</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ upcomingGames.length }}</p>
                            <p class="text-xs text-gray-500">Prochains matchs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Pending registrations -->
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">Inscriptions en attente</h3>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                            {{ pendingRegistrations.length }}
                        </span>
                    </div>

                    <div v-if="pendingRegistrations.length" class="divide-y divide-gray-50">
                        <div
                            v-for="reg in pendingRegistrations"
                            :key="reg.id"
                            class="flex items-center justify-between px-5 py-3"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ reg.club?.name }}</p>
                                <p class="text-xs text-gray-500">{{ reg.competition?.name }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400">{{ formatDate(reg.created_at) }}</span>
                                <CompetitionBadge status="open" />
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">Aucune inscription en attente</p>
                    </div>
                </div>

                <!-- Upcoming games -->
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">Prochains matchs</h3>
                        <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                            {{ upcomingGames.length }}
                        </span>
                    </div>

                    <div v-if="upcomingGames.length" class="divide-y divide-gray-50">
                        <div
                            v-for="game in upcomingGames"
                            :key="game.id"
                            class="flex items-center justify-between px-5 py-3"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">
                                    {{ game.home_club?.name }} <span class="text-gray-400">vs</span> {{ game.away_club?.name }}
                                </p>
                                <p class="text-xs text-gray-500">{{ game.phase?.competition?.name }} &mdash; {{ game.phase?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-gray-700">{{ formatDateTime(game.scheduled_at) }}</p>
                                <p v-if="game.location" class="text-xs text-gray-400">{{ game.location }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">Aucun match a venir</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

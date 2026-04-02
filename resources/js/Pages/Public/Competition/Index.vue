<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

defineProps({
    competitions: Array,
    canLogin: Boolean,
    canRegister: Boolean,
});

const sportEmojis = {
    football: '⚽',
    basketball: '🏀',
    handball: '🤾',
    natation: '🏊',
    rugby: '🏉',
    volleyball: '🏐',
};

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Compétitions" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <!-- Header -->
        <div class="bg-white pb-8 pt-32">
            <div class="mx-auto max-w-6xl px-6">
                <h1 class="text-3xl font-bold text-gray-900">Compétitions</h1>
                <p class="mt-2 text-gray-500">Retrouvez tous les championnats et tournois organisés sur Squadly.</p>
            </div>
        </div>

        <!-- List -->
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div v-if="competitions.length === 0" class="rounded-2xl border border-gray-100 bg-white p-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-3.52 1.122 6.023 6.023 0 0 1-3.52-1.122" /></svg>
                <p class="mt-4 text-gray-500">Aucune compétition pour le moment.</p>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="c in competitions"
                    :key="c.id"
                    :href="route('competitions.show', c.id)"
                    class="group rounded-2xl border border-gray-100 bg-white p-6 transition hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50"
                >
                    <!-- Sport + Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">{{ sportEmojis[c.sport_type] || '🏅' }}</span>
                        <CompetitionBadge :status="c.status" />
                    </div>

                    <!-- Name -->
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-emerald-700">{{ c.name }}</h3>

                    <!-- Organizer -->
                    <p class="mt-1 text-sm text-gray-500">{{ c.organizer?.name }}</p>

                    <!-- Meta -->
                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            {{ c.season }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                            {{ c.clubs_count }} clubs
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" /></svg>
                            {{ c.games_count }} matchs
                        </span>
                    </div>

                    <!-- Dates -->
                    <p class="mt-3 text-xs text-gray-400">
                        {{ formatDate(c.starts_at) }} → {{ formatDate(c.ends_at) }}
                    </p>
                </Link>
            </div>
        </div>
    </div>
</template>

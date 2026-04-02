<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';
import CompetitionBadge from '@/Components/Competition/CompetitionBadge.vue';

defineProps({
    club: Object,
    competitions: Array,
    canLogin: Boolean,
    canRegister: Boolean,
});

const sportEmojis = {
    football: '⚽', basketball: '🏀', handball: '🤾',
    natation: '🏊', rugby: '🏉', volleyball: '🏐',
};

const sportLabels = {
    football: 'Football', basketball: 'Basketball', handball: 'Handball',
    natation: 'Natation', rugby: 'Rugby', volleyball: 'Volleyball',
};
</script>

<template>
    <Head :title="club.name" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <!-- Hero -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 pb-16 pt-32">
            <div class="mx-auto max-w-5xl px-6">
                <div class="flex items-center gap-5">
                    <div v-if="club.logo_url" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border-2 border-white/30 bg-white shadow-lg">
                        <img :src="club.logo_url" :alt="club.name" class="h-full w-full object-cover" />
                    </div>
                    <div v-else class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10 text-3xl font-extrabold text-white shadow-lg">
                        {{ club.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ club.name }}</h1>
                        <p v-if="club.city" class="mt-1 text-emerald-100">{{ club.city }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                v-for="sport in club.sports"
                                :key="sport"
                                class="rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium text-white backdrop-blur-sm"
                            >
                                {{ sportEmojis[sport] || '🏅' }} {{ sportLabels[sport] || sport }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-8 flex gap-8">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">{{ club.members_count || 0 }}</p>
                        <p class="text-xs text-emerald-200">Membres</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">{{ club.sections?.reduce((sum, s) => sum + (s.teams?.length || 0), 0) }}</p>
                        <p class="text-xs text-emerald-200">Équipes</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">{{ club.sports?.length || 0 }}</p>
                        <p class="text-xs text-emerald-200">Sports</p>
                    </div>
                    <div v-if="competitions?.length" class="text-center">
                        <p class="text-2xl font-bold text-white">{{ competitions.length }}</p>
                        <p class="text-xs text-emerald-200">Compétitions</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-5xl px-6 py-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Sections & Teams -->
                    <div v-for="section in club.sections" :key="section.sport_type" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-2">
                            <span class="text-xl">{{ sportEmojis[section.sport_type] || '🏅' }}</span>
                            <h3 class="text-sm font-semibold text-gray-900">{{ sportLabels[section.sport_type] || section.sport_type }}</h3>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">{{ section.teams?.length || 0 }} équipe(s)</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="team in section.teams"
                                :key="team.id"
                                class="rounded-lg border border-gray-100 bg-gray-50 p-4"
                            >
                                <p class="text-sm font-medium text-gray-900">{{ team.name }}</p>
                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                    <span v-if="team.age_category">{{ team.age_category }}</span>
                                    <span>{{ team.members_count || 0 }} joueurs</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!section.teams?.length" class="text-sm text-gray-400">Aucune équipe dans cette section.</p>
                    </div>

                    <div v-if="!club.sections?.length" class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                        <p class="text-sm text-gray-400">Aucune section configurée.</p>
                    </div>
                </div>

                <!-- Sidebar: Competitions -->
                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-semibold text-gray-900">Compétitions</h3>
                        <div v-if="competitions?.length" class="space-y-3">
                            <Link
                                v-for="comp in competitions"
                                :key="comp.id"
                                :href="route('competitions.show', comp.id)"
                                class="block rounded-lg border border-gray-100 p-3 transition hover:border-emerald-200 hover:bg-emerald-50/50"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ comp.name }}</p>
                                        <p class="text-xs text-gray-500">{{ comp.organizer_name }}</p>
                                    </div>
                                    <CompetitionBadge :status="comp.status" />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">{{ comp.season }}</p>
                            </Link>
                        </div>
                        <p v-else class="text-sm text-gray-400">Ce club ne participe à aucune compétition.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

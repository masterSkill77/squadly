<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { sportLabels } from '@/Utils/sportLabels';
import LatestAnnouncements from './LatestAnnouncements.vue';

const { coachTeams, club, nextEvent, latestAnnouncements } = usePage().props;
const totalPlayers = (coachTeams || []).reduce((sum, t) => sum + t.players_count, 0);
</script>

<template>
    <div class="space-y-8">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ club?.name || 'Mon club' }}</h3>
            <p class="text-sm text-gray-500">Bienvenue, coach. Voici vos équipes.</p>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <p class="text-sm text-gray-500">Équipes encadrées</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ coachTeams?.length || 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <p class="text-sm text-gray-500">Joueurs total</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ totalPlayers }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <p class="text-sm text-gray-500">Prochain événement</p>
                <template v-if="nextEvent">
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ nextEvent.title }}</p>
                    <p class="text-xs text-gray-500">{{ nextEvent.team_name }} · {{ new Date(nextEvent.start_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</p>
                </template>
                <p v-else class="mt-2 text-2xl font-bold text-gray-400">—</p>
            </div>
        </div>

        <!-- Teams list -->
        <div v-if="coachTeams?.length">
            <h4 class="mb-3 text-base font-semibold text-gray-900">Mes équipes</h4>
            <div class="grid gap-4 sm:grid-cols-2">
                <Link
                    v-for="team in coachTeams"
                    :key="team.id"
                    :href="route('coach.team', team.id)"
                    class="rounded-xl border border-gray-100 bg-white p-5 transition hover:border-blue-200 hover:shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ team.name }}</p>
                            <p class="text-xs text-gray-500">{{ sportLabels[team.sport_type] || team.sport_type }}</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ team.players_count }} joueur{{ team.players_count > 1 ? 's' : '' }}</span>
                    </div>
                    <p v-if="team.age_category" class="mt-2 text-xs text-gray-400">{{ team.age_category }}</p>
                </Link>
            </div>
        </div>

        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <p class="text-sm text-gray-400">Vous n'avez pas encore d'équipe assignée. Contactez le président de votre club.</p>
        </div>

        <LatestAnnouncements :announcements="latestAnnouncements" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { sportLabels } from '@/Utils/sportLabels';

const props = defineProps({ teams: Array });

const teamsBySport = computed(() => {
    const grouped = {};
    for (const team of props.teams) {
        if (!grouped[team.sport_type]) {
            grouped[team.sport_type] = {
                sport_type: team.sport_type,
                label: sportLabels[team.sport_type] || team.sport_type,
                teams: [],
            };
        }
        grouped[team.sport_type].teams.push(team);
    }
    return Object.values(grouped);
});
</script>

<template>
    <Head title="Effectifs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Mes effectifs</h2>
        </template>

        <div v-if="teams.length" class="space-y-8">
            <section v-for="sport in teamsBySport" :key="sport.sport_type">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ sport.label }}</p>

                <div class="grid gap-3 grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    <Link
                        v-for="team in sport.teams"
                        :key="team.id"
                        :href="route('coach.team', team.id)"
                        class="group rounded-xl border border-gray-100 bg-white p-5 transition hover:border-blue-200 hover:shadow-md"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">{{ team.name }}</p>
                            <svg class="h-4 w-4 text-gray-300 transition group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                            <span v-if="team.age_category">{{ team.age_category }}</span>
                            <span>{{ team.players_count }} joueur{{ team.players_count > 1 ? 's' : '' }}</span>
                            <span v-if="team.season" class="text-gray-400">{{ team.season }}</span>
                        </div>
                    </Link>
                </div>
            </section>
        </div>

        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <p class="text-sm text-gray-400">Vous n'avez pas encore d'equipe assignee. Contactez le president de votre club.</p>
        </div>
    </AuthenticatedLayout>
</template>

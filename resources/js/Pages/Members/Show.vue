<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MemberInfoCard from '@/Components/Members/MemberInfoCard.vue';
import SportProfileCard from '@/Components/Members/SportProfileCard.vue';
import TeamAssignCard from '@/Components/Members/TeamAssignCard.vue';
import CoachTeamsCard from '@/Components/Members/CoachTeamsCard.vue';
import { Role } from '@/Utils/roles';

const props = defineProps({ member: Object, sections: Array, teamIds: Array });

const isCoach = props.member.role === Role.Coach;

const assignedSections = computed(() =>
    props.sections.filter(s => s.teams.some(t => props.teamIds.includes(t.id)))
);

function deleteMember() {
    if (confirm('Supprimer ce membre du club ?')) {
        router.delete(route('members.destroy', props.member.id));
    }
}
</script>

<template>
    <Head :title="`${member.first_name} ${member.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('members.index')" class="text-gray-400 transition hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </Link>
                <h2 class="text-lg font-semibold text-gray-900">{{ member.first_name }} {{ member.last_name }}</h2>
                <span
                    class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="isCoach ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'"
                >
                    {{ isCoach ? 'Coach' : 'Membre' }}
                </span>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <MemberInfoCard :member="member" />

                <!-- Joueur: profils sportifs -->
                <div v-if="!isCoach && assignedSections.length">
                    <h3 class="mb-3 text-base font-semibold text-gray-900">Profils sportifs</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <SportProfileCard v-for="s in assignedSections" :key="s.id" :section="s" :member-id="member.id" />
                    </div>
                </div>

                <div v-if="!isCoach && !assignedSections.length" class="rounded-xl border border-dashed border-gray-300 p-5 text-center">
                    <p class="text-sm text-gray-400">Assignez ce membre à une équipe pour voir son profil sportif.</p>
                </div>

                <!-- Coach: pas de profil sportif, juste un message -->
                <div v-if="isCoach" class="rounded-xl border border-blue-100 bg-blue-50/30 p-5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>
                        <p class="text-sm font-medium text-blue-700">Ce membre est coach</p>
                    </div>
                    <p class="mt-1 text-xs text-blue-600/70">Il encadre les équipes assignées ci-contre. Il n'a pas de profil sportif joueur.</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Coach: équipes encadrées (bleu) -->
                <CoachTeamsCard v-if="isCoach" :sections="sections" :member-id="member.id" :team-ids="teamIds" />

                <!-- Joueur: équipes assignées (vert) -->
                <TeamAssignCard v-else :sections="sections" :member-id="member.id" :team-ids="teamIds" />

                <div class="rounded-xl border border-red-100 bg-red-50/50 p-4">
                    <button class="text-sm font-medium text-red-600 hover:text-red-700" @click="deleteMember">
                        Retirer du club
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

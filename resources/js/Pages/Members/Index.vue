<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MemberCard from '@/Components/Members/MemberCard.vue';
import AddMemberModal from '@/Components/Members/AddMemberModal.vue';
import { sportLabels } from '@/Utils/sportLabels';

const props = defineProps({ members: Array, club: Object, sections: Array });

const showAdd = ref(false);
const search = ref('');
const groupBy = ref('none');

const allTeams = computed(() => {
    const teams = [];
    for (const s of (props.sections || [])) {
        for (const t of s.teams) {
            teams.push({ ...t, sport_type: s.sport_type });
        }
    }
    return teams;
});

const filtered = computed(() => {
    if (!search.value) return props.members;
    const q = search.value.toLowerCase();
    return props.members.filter(m =>
        m.full_name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q)
    );
});

const grouped = computed(() => {
    if (groupBy.value === 'none') {
        return [{ key: 'all', label: null, members: filtered.value }];
    }

    if (groupBy.value === 'team') {
        const groups = [];
        const placed = new Set();

        for (const team of allTeams.value) {
            const teamMembers = filtered.value.filter(m => m.team_ids.includes(team.id));
            if (teamMembers.length) {
                groups.push({
                    key: `team-${team.id}`,
                    label: `${team.name}`,
                    sublabel: sportLabels[team.sport_type] || team.sport_type,
                    members: teamMembers,
                });
                teamMembers.forEach(m => placed.add(m.id));
            }
        }

        const unassigned = filtered.value.filter(m => !placed.has(m.id));
        if (unassigned.length) {
            groups.push({ key: 'unassigned', label: 'Sans équipe', sublabel: null, members: unassigned });
        }

        return groups;
    }

    if (groupBy.value === 'role') {
        const roleOrder = { admin_club: 0, coach: 1, membre: 2 };
        const roleLabelsMap = { admin_club: 'Président', coach: 'Coachs', membre: 'Joueurs / Membres' };
        const groups = {};

        for (const m of filtered.value) {
            const role = m.role || 'membre';
            if (!groups[role]) groups[role] = [];
            groups[role].push(m);
        }

        return Object.entries(groups)
            .sort(([a], [b]) => (roleOrder[a] ?? 9) - (roleOrder[b] ?? 9))
            .map(([role, members]) => ({
                key: `role-${role}`,
                label: roleLabelsMap[role] || role,
                sublabel: null,
                members,
            }));
    }

    return [{ key: 'all', label: null, members: filtered.value }];
});
</script>

<template>
    <Head title="Membres" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Membres</h2>
        </template>

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative max-w-xs flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input v-model="search" type="text" placeholder="Rechercher…" class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white p-0.5">
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium transition"
                            :class="groupBy === 'none' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            @click="groupBy = 'none'"
                        >
                            Tous
                        </button>
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium transition"
                            :class="groupBy === 'team' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            @click="groupBy = 'team'"
                        >
                            Par équipe
                        </button>
                        <button
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium transition"
                            :class="groupBy === 'role' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            @click="groupBy = 'role'"
                        >
                            Par rôle
                        </button>
                    </div>
                    <!-- Export buttons -->
                    <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-white p-0.5">
                        <a
                            :href="route('members.export.csv')"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                            title="Exporter en CSV"
                        >
                            <svg class="inline h-3.5 w-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            CSV
                        </a>
                        <a
                            :href="route('members.export.pdf')"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                            title="Exporter en PDF"
                        >
                            <svg class="inline h-3.5 w-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            PDF
                        </a>
                    </div>

                    <button class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700" @click="showAdd = true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Ajouter un membre
                    </button>
                </div>
            </div>

            <template v-if="filtered.length">
                <div v-for="group in grouped" :key="group.key">
                    <div v-if="group.label" class="mb-3 flex items-center gap-2 pt-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ group.label }}</p>
                        <span v-if="group.sublabel" class="text-[10px] text-gray-300">{{ group.sublabel }}</span>
                        <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500">{{ group.members.length }}</span>
                    </div>
                    <div class="grid gap-3 grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 xl:grid-cols-8 2xl:grid-cols-10">
                        <MemberCard v-for="m in group.members" :key="m.id" :member="m" />
                    </div>
                </div>
            </template>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                <p class="text-sm text-gray-400">{{ search ? 'Aucun résultat.' : 'Aucun membre. Ajoutez votre premier membre !' }}</p>
            </div>

            <p class="text-xs text-gray-400">{{ members.length }} membre{{ members.length > 1 ? 's' : '' }} au total</p>
        </div>

        <AddMemberModal :show="showAdd" :sections="sections" @close="showAdd = false" />
    </AuthenticatedLayout>
</template>

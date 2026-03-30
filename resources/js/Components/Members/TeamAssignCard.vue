<script setup>
import { sportLabels } from '@/Utils/sportLabels';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ sections: Array, memberId: Number, teamIds: Array });

const form = useForm({ team_ids: [...(props.teamIds || [])] });

function toggle(teamId) {
    const idx = form.team_ids.indexOf(teamId);
    if (idx >= 0) form.team_ids.splice(idx, 1);
    else form.team_ids.push(teamId);
}

function submit() {
    form.put(route('members.teams', props.memberId));
}
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Équipes</h3>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <div v-for="section in sections" :key="section.id">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ sportLabels[section.sport_type] || section.sport_type }}</p>
                <div class="mt-2 space-y-1">
                    <label v-for="team in section.teams" :key="team.id" class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 transition hover:bg-gray-50">
                        <input type="checkbox" :checked="form.team_ids.includes(team.id)" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @change="toggle(team.id)" />
                        <span class="text-sm text-gray-700">{{ team.name }}</span>
                        <span v-if="team.age_category" class="text-xs text-gray-400">{{ team.age_category }}</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Enregistrer</button>
            </div>
        </form>
    </div>
</template>

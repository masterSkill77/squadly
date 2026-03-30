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
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>
            <h3 class="text-base font-semibold text-gray-900">Équipes encadrées</h3>
        </div>
        <p class="mt-1 text-xs text-gray-500">Sélectionnez les équipes que ce coach encadre.</p>

        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <div v-for="section in sections" :key="section.id">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ sportLabels[section.sport_type] || section.sport_type }}</p>
                <div class="mt-2 space-y-1">
                    <label v-for="team in section.teams" :key="team.id" class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 transition hover:bg-blue-50">
                        <input type="checkbox" :checked="form.team_ids.includes(team.id)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" @change="toggle(team.id)" />
                        <span class="text-sm text-gray-700">{{ team.name }}</span>
                        <span v-if="team.age_category" class="text-xs text-gray-400">{{ team.age_category }}</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-40">Enregistrer</button>
            </div>
        </form>
    </div>
</template>

<script setup>
const props = defineProps({
    form: Object,
});

const sportLabels = {
    football: 'Football',
    basketball: 'Basketball',
    handball: 'Handball',
    natation: 'Natation',
    rugby: 'Rugby',
    volleyball: 'Volleyball',
};

function addTeam() {
    props.form.teams.push({ name: '', sport: props.form.sports[0] || '', age_category: '' });
}

function removeTeam(index) {
    props.form.teams.splice(index, 1);
}
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Vos équipes</h2>
            <p class="mt-1 text-sm text-gray-500">Créez au moins une équipe par section.</p>
        </div>

        <div v-for="(team, i) in form.teams" :key="i" class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3">
            <div class="flex-1 space-y-2">
                <input v-model="team.name" type="text" placeholder="Nom (ex : Seniors A)" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                <div class="flex gap-2">
                    <select v-model="team.sport" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option v-for="s in form.sports" :key="s" :value="s">{{ sportLabels[s] || s }}</option>
                    </select>
                    <input v-model="team.age_category" type="text" placeholder="Catégorie (U17, Senior…)" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
            </div>
            <button v-if="form.teams.length > 1" type="button" class="mt-1 text-gray-400 hover:text-red-500" @click="removeTeam(i)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <button type="button" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700" @click="addTeam">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Ajouter une équipe
        </button>
    </div>
</template>

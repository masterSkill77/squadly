<script setup>
defineProps({ form: Object });

const sportLabels = {
    football: '⚽ Football',
    basketball: '🏀 Basketball',
    handball: '🤾 Handball',
    natation: '🏊 Natation',
    rugby: '🏉 Rugby',
    volleyball: '🏐 Volleyball',
};
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Récapitulatif</h2>
            <p class="mt-1 text-sm text-gray-500">Vérifiez avant de créer votre club.</p>
        </div>

        <div class="space-y-4 rounded-xl border border-gray-100 bg-gray-50 p-5">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Club</p>
                <p class="text-sm font-semibold text-gray-900">{{ form.club_name }} <span v-if="form.city" class="font-normal text-gray-500">— {{ form.city }}</span></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Sports</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    <span v-for="s in form.sports" :key="s" class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                        {{ sportLabels[s] || s }}
                    </span>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Équipes ({{ form.teams.length }})</p>
                <ul class="mt-1 space-y-1">
                    <li v-for="(t, i) in form.teams" :key="i" class="text-sm text-gray-700">
                        {{ t.name }} <span class="text-gray-400">— {{ sportLabels[t.sport] || t.sport }}</span>
                        <span v-if="t.age_category" class="text-gray-400"> · {{ t.age_category }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

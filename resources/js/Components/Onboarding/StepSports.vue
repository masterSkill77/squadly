<script setup>
defineProps({
    form: Object,
    sportTemplates: Array,
});

const sportLabels = {
    football: '⚽ Football',
    basketball: '🏀 Basketball',
    handball: '🤾 Handball',
    natation: '🏊 Natation',
    rugby: '🏉 Rugby',
    volleyball: '🏐 Volleyball',
};

function toggle(sport) {
    const idx = this.form.sports.indexOf(sport);
    if (idx >= 0) {
        this.form.sports.splice(idx, 1);
    } else {
        this.form.sports.push(sport);
    }
}
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Sports pratiqués</h2>
            <p class="mt-1 text-sm text-gray-500">Sélectionnez les sports de votre club.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <button
                v-for="sport in sportTemplates"
                :key="sport"
                type="button"
                class="rounded-xl border-2 px-4 py-3 text-left text-sm font-medium transition"
                :class="form.sports.includes(sport) ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                @click="form.sports.includes(sport) ? form.sports.splice(form.sports.indexOf(sport), 1) : form.sports.push(sport)"
            >
                {{ sportLabels[sport] || sport }}
            </button>
        </div>
    </div>
</template>

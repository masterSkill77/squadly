<script setup>
import { sportLabels } from '@/Utils/sportLabels';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ section: Object, memberId: Number });

const form = useForm({
    section_id: props.section.id,
    sport_profile: { ...(props.section.sport_profile || {}) },
});

function submit() {
    form.put(route('members.sport-profile', props.memberId));
}
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <h4 class="text-sm font-semibold text-gray-900">{{ sportLabels[section.sport_type] || section.sport_type }}</h4>

        <form v-if="section.sport_template?.fields?.length" class="mt-4 space-y-3" @submit.prevent="submit">
            <div v-for="field in section.sport_template.fields" :key="field.key">
                <label class="block text-xs font-medium text-gray-600">{{ field.label }}</label>
                <select
                    v-if="field.type === 'select'"
                    v-model="form.sport_profile[field.key]"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="">—</option>
                    <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <input
                    v-else
                    v-model="form.sport_profile[field.key]"
                    :type="field.type || 'text'"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                />
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Sauvegarder</button>
            </div>
        </form>

        <p v-else class="mt-2 text-xs text-gray-400">Aucun champ défini pour ce sport.</p>
    </div>
</template>

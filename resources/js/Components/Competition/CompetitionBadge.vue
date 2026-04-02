<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const config = {
    draft: { label: 'Brouillon', classes: 'bg-gray-100 text-gray-600' },
    open: { label: 'Ouvert', classes: 'bg-blue-50 text-blue-700' },
    ongoing: { label: 'En cours', classes: 'bg-emerald-50 text-emerald-700' },
    finished: { label: 'Terminé', classes: 'bg-purple-50 text-purple-700' },
};

const badge = computed(() => config[props.status] || config.draft);
</script>

<template>
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="badge.classes">
        {{ badge.label }}
    </span>
</template>

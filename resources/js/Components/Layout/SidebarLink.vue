<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    active: Boolean,
    collapsed: Boolean,
});
</script>

<template>
    <Link
        :href="href"
        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
        :class="active
            ? 'bg-emerald-50 text-emerald-700'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
    >
        <span class="shrink-0"><slot name="icon" /></span>
        <span v-if="!collapsed" class="truncate"><slot /></span>
    </Link>
</template>

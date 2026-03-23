<script setup>
defineProps({
    label: String,
    value: [String, Number],
    icon: String,
    color: { type: String, default: 'emerald' },
});
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ label }}</p>
            <div :class="`bg-${color}-50 text-${color}-600`" class="flex h-9 w-9 items-center justify-center rounded-lg">
                <slot name="icon" />
            </div>
        </div>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ value }}</p>
    </div>
</template>

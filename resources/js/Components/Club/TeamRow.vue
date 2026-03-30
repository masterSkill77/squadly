<script setup>
defineProps({ team: Object });
defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
        <div>
            <span class="text-sm font-medium text-gray-900">{{ team.name }}</span>
            <span v-if="team.age_category" class="ml-2 text-xs text-gray-400">{{ team.age_category }}</span>
            <span v-if="team.season" class="ml-2 text-xs text-gray-400">· {{ team.season }}</span>
        </div>
        <div class="flex gap-1">
            <button class="rounded p-1 text-gray-400 transition hover:bg-gray-200 hover:text-gray-600" @click="$emit('edit', team)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
            </button>
            <button class="rounded p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500" @click="$emit('delete', team)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
        </div>
    </div>
</template>

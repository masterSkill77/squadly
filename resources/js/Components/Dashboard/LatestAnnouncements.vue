<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ announcements: Array });

function timeAgo(iso) {
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `il y a ${mins}min`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `il y a ${hours}h`;
    const days = Math.floor(hours / 24);
    return `il y a ${days}j`;
}
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900">Dernières annonces</h4>
            <Link :href="route('announcements.index')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Voir tout</Link>
        </div>

        <div v-if="announcements?.length" class="mt-4 space-y-3">
            <div v-for="a in announcements" :key="a.id" class="border-l-2 border-emerald-200 pl-3">
                <p class="text-sm font-medium text-gray-900">{{ a.title }}</p>
                <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">{{ a.content }}</p>
                <p class="mt-1 text-[10px] text-gray-400">{{ a.author_name }} · {{ timeAgo(a.created_at) }}</p>
            </div>
        </div>

        <p v-else class="mt-3 text-sm text-gray-400">Aucune annonce récente.</p>
    </div>
</template>

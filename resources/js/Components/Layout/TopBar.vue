<script setup>
import NotificationBell from '@/Components/Layout/NotificationBell.vue';
</script>

<template>
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-100 bg-white px-6">
        <slot name="left" />

        <!-- Search -->
        <div class="mx-4 hidden max-w-md flex-1 sm:block">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <input type="text" placeholder="Rechercher un membre, une équipe…" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 transition focus:border-emerald-500 focus:bg-white focus:ring-emerald-500" />
                <kbd class="absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-400 lg:inline-block">⌘K</kbd>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <!-- Notifications -->
            <NotificationBell />
            <!-- Aide -->
            <button class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" title="Aide">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
            </button>
        </div>
    </header>
</template>

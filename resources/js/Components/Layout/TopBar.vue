<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
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

        <div class="flex items-center gap-3">
            <Dropdown align="right" width="48">
                <template #trigger>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </span>
                        <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </template>
                <template #content>
                    <DropdownLink :href="route('profile.edit')">Mon profil</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Déconnexion</DropdownLink>
                </template>
            </Dropdown>
        </div>
    </header>
</template>

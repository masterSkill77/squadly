<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/Layout/NotificationBell.vue';
import { Role } from '@/Utils/roles';

const role = usePage().props.auth.role;
const isOrganizer = role === Role.OrganizerAdmin || role === Role.OrganizerStaff;

const query = ref('');
const results = ref([]);
const isOpen = ref(false);
const isLoading = ref(false);
const selectedIndex = ref(-1);
const inputRef = ref(null);

const typeIcons = {
    member: '👤',
    team: '👥',
    event: '📅',
    competition: '🏆',
    match: '⚽',
};

const typeLabels = {
    member: 'Membre',
    team: 'Équipe',
    event: 'Événement',
    competition: 'Compétition',
    match: 'Match',
};

let searchTimeout = null;

watch(query, (val) => {
    clearTimeout(searchTimeout);
    selectedIndex.value = -1;

    if (val.length < 2) {
        results.value = [];
        isOpen.value = false;
        return;
    }

    isLoading.value = true;
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/search?q=${encodeURIComponent(val)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            results.value = await response.json();
            isOpen.value = true;
        } catch {
            results.value = [];
        } finally {
            isLoading.value = false;
        }
    }, 250);
});

function navigate(url) {
    query.value = '';
    results.value = [];
    isOpen.value = false;
    router.visit(url);
}

function onKeydown(e) {
    if (!isOpen.value) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, results.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        navigate(results.value[selectedIndex.value].url);
    } else if (e.key === 'Escape') {
        isOpen.value = false;
        inputRef.value?.blur();
    }
}

function close() {
    setTimeout(() => { isOpen.value = false; }, 200);
}

// ⌘K shortcut
function onGlobalKeydown(e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        inputRef.value?.focus();
    }
}

onMounted(() => document.addEventListener('keydown', onGlobalKeydown));
onUnmounted(() => document.removeEventListener('keydown', onGlobalKeydown));

function showHelp() {
    window.dispatchEvent(new CustomEvent('squadly:show-help'));
}
</script>

<template>
    <header class="sticky top-0 z-20 flex h-16 items-center border-b border-gray-100 bg-white px-6">
        <div class="min-w-0 flex-1">
            <slot name="left" />
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <!-- Search -->
            <div class="relative hidden sm:block">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Rechercher…"
                    class="w-56 rounded-lg border border-gray-200 bg-gray-50 py-2 pl-10 pr-12 text-sm text-gray-700 placeholder-gray-400 transition focus:w-80 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    @keydown="onKeydown"
                    @focus="query.length >= 2 && (isOpen = true)"
                    @blur="close"
                />
                <kbd v-if="!query" class="absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-400 lg:inline-block">⌘K</kbd>
                <svg v-if="isLoading" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>

                <!-- Results dropdown -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="isOpen && (results.length || query.length >= 2)"
                        class="absolute right-0 top-full z-50 mt-1 w-96 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    >
                        <div v-if="results.length" class="max-h-80 overflow-y-auto py-1">
                            <button
                                v-for="(result, i) in results"
                                :key="i"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition"
                                :class="selectedIndex === i ? 'bg-emerald-50' : 'hover:bg-gray-50'"
                                @mousedown.prevent="navigate(result.url)"
                                @mouseenter="selectedIndex = i"
                            >
                                <span class="text-base">{{ typeIcons[result.type] || '📄' }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ result.label }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ result.sublabel }}</p>
                                </div>
                                <span class="shrink-0 rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500">
                                    {{ typeLabels[result.type] || result.type }}
                                </span>
                            </button>
                        </div>
                        <div v-else-if="!isLoading" class="px-4 py-6 text-center">
                            <p class="text-sm text-gray-500">Aucun résultat pour "{{ query }}"</p>
                        </div>
                        <div v-if="results.length" class="border-t border-gray-100 px-4 py-2 text-[10px] text-gray-400">
                            <kbd class="rounded border border-gray-200 bg-gray-50 px-1">↑↓</kbd> naviguer
                            <kbd class="ml-2 rounded border border-gray-200 bg-gray-50 px-1">↵</kbd> ouvrir
                            <kbd class="ml-2 rounded border border-gray-200 bg-gray-50 px-1">esc</kbd> fermer
                        </div>
                    </div>
                </Transition>
            </div>

            <div class="flex items-center gap-1">
            <!-- Compétitions -->
            <Link
                :href="isOrganizer ? route('organizer.competitions.index') : route('club.competitions')"
                class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-emerald-50 hover:text-emerald-700"
                :class="route().current('club.competitions*') || route().current('organizer.competitions.*') ? 'bg-emerald-50 text-emerald-700' : ''"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-3.52 1.122 6.023 6.023 0 0 1-3.52-1.122" /></svg>
                <span class="hidden md:inline">Compétitions</span>
            </Link>
            <!-- Notifications -->
            <NotificationBell />
            <!-- Aide -->
            <button class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" title="Aide" @click="showHelp">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
            </button>
            </div>
        </div>
    </header>
</template>

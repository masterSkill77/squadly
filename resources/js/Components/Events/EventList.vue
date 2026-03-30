<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ events: Array, teams: Array });
const emit = defineEmits(['create', 'edit']);

const filterTeamId = ref(null);
const viewMode = ref('list');

const filteredEvents = computed(() => {
    if (!filterTeamId.value) return props.events;
    return props.events.filter(e => e.team_id === filterTeamId.value);
});

const groupedByDate = computed(() => {
    const groups = {};
    for (const ev of filteredEvents.value) {
        const date = ev.start_at.slice(0, 10);
        if (!groups[date]) groups[date] = [];
        groups[date].push(ev);
    }
    return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b));
});

const typeBadgeClasses = {
    training: 'bg-blue-50 text-blue-700',
    match: 'bg-red-50 text-red-700',
    media_day: 'bg-purple-50 text-purple-700',
    other: 'bg-gray-100 text-gray-600',
};

const typeColorClasses = {
    training: 'border-l-blue-500',
    match: 'border-l-red-500',
    media_day: 'border-l-purple-500',
    other: 'border-l-gray-400',
};

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

function formatTime(iso) {
    return new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function formatDay(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric' });
}

function formatMonth(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase();
}
</script>

<template>
    <div class="space-y-6">
        <!-- Toolbar: filters + view toggle + add -->
        <div class="flex flex-wrap items-center gap-2">
            <button
                class="rounded-full border-2 px-3 py-1 text-xs font-medium transition"
                :class="!filterTeamId ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                @click="filterTeamId = null"
            >
                Toutes
            </button>
            <button
                v-for="t in teams"
                :key="t.id"
                class="rounded-full border-2 px-3 py-1 text-xs font-medium transition"
                :class="filterTeamId === t.id ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                @click="filterTeamId = t.id"
            >
                {{ t.name }}
            </button>

            <div class="ml-auto flex items-center gap-2">
                <!-- View toggle -->
                <div class="flex items-center gap-0.5 rounded-lg border border-gray-200 bg-white p-0.5">
                    <button
                        class="rounded-md p-1.5 transition"
                        :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'list'"
                        title="Vue liste"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                    </button>
                    <button
                        class="rounded-md p-1.5 transition"
                        :class="viewMode === 'card' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'card'"
                        title="Vue cartes"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    </button>
                </div>

                <button
                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="$emit('create')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nouvel événement
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="groupedByDate.length">
            <!-- LIST VIEW -->
            <div v-if="viewMode === 'list'" class="space-y-8">
                <section v-for="[date, eventsOfDay] in groupedByDate" :key="date">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ formatDate(date) }}</p>
                    <div class="space-y-2">
                        <button
                            v-for="ev in eventsOfDay"
                            :key="ev.id"
                            class="flex w-full items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 text-left transition hover:border-emerald-200 hover:shadow-sm"
                            @click="$emit('edit', ev)"
                        >
                            <div class="shrink-0 text-center">
                                <p class="text-sm font-semibold text-gray-900">{{ formatTime(ev.start_at) }}</p>
                                <p v-if="ev.end_at" class="text-[10px] text-gray-400">{{ formatTime(ev.end_at) }}</p>
                            </div>
                            <div class="h-10 w-px bg-gray-200" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeBadgeClasses[ev.type] || typeBadgeClasses.other">{{ ev.type_label }}</span>
                                    <span class="text-[10px] text-gray-400">{{ ev.team_name }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm font-medium text-gray-900">{{ ev.title }}</p>
                                <p v-if="ev.location" class="truncate text-xs text-gray-500">{{ ev.location }}</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                </section>
            </div>

            <!-- CARD VIEW -->
            <div v-else class="space-y-8">
                <section v-for="[date, eventsOfDay] in groupedByDate" :key="date">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ formatDate(date) }}</p>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <button
                            v-for="ev in eventsOfDay"
                            :key="ev.id"
                            class="flex flex-col rounded-xl border-l-4 border border-gray-100 bg-white p-5 text-left transition hover:shadow-md"
                            :class="typeColorClasses[ev.type] || typeColorClasses.other"
                            @click="$emit('edit', ev)"
                        >
                            <div class="flex items-start justify-between">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeBadgeClasses[ev.type] || typeBadgeClasses.other">{{ ev.type_label }}</span>
                                <div class="text-center leading-none">
                                    <p class="text-lg font-bold text-gray-900">{{ formatDay(ev.start_at) }}</p>
                                    <p class="text-[10px] font-medium text-gray-400">{{ formatMonth(ev.start_at) }}</p>
                                </div>
                            </div>
                            <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ ev.title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ ev.team_name }}</p>
                            <div class="mt-auto flex items-center gap-3 pt-3 text-xs text-gray-400">
                                <span>{{ formatTime(ev.start_at) }}<template v-if="ev.end_at"> - {{ formatTime(ev.end_at) }}</template></span>
                                <span v-if="ev.location" class="truncate">{{ ev.location }}</span>
                            </div>
                        </button>
                    </div>
                </section>
            </div>
        </template>

        <!-- Empty state -->
        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
            <p class="mt-3 text-sm text-gray-400">Aucun événement planifié.</p>
            <button class="mt-2 text-sm font-medium text-emerald-600 hover:text-emerald-700" @click="$emit('create')">
                Créer le premier événement
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    events: Array,
    teams: Array,
    attendanceRouteName: { type: String, default: null },
    convocationRouteName: { type: String, default: null },
});
const emit = defineEmits(['create', 'edit']);

const filterTeamId = ref(null);
const viewMode = ref('calendar');

// Calendar state
const today = new Date();
const calendarMonth = ref(today.getMonth());
const calendarYear = ref(today.getFullYear());
const selectedDate = ref(today.toISOString().slice(0, 10));

const calendarMonthLabel = computed(() => {
    return new Date(calendarYear.value, calendarMonth.value).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
    const year = calendarYear.value;
    const month = calendarMonth.value;
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    // Monday = 0
    let startOffset = (firstDay.getDay() + 6) % 7;
    const days = [];

    // Previous month padding
    for (let i = startOffset - 1; i >= 0; i--) {
        const d = new Date(year, month, -i);
        days.push({ date: d.toISOString().slice(0, 10), day: d.getDate(), current: false });
    }

    // Current month
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const d = new Date(year, month, i);
        days.push({ date: d.toISOString().slice(0, 10), day: i, current: true });
    }

    // Next month padding to fill 6 rows
    while (days.length % 7 !== 0) {
        const d = new Date(year, month + 1, days.length - startOffset - lastDay.getDate() + 1);
        days.push({ date: d.toISOString().slice(0, 10), day: d.getDate(), current: false });
    }

    return days;
});

const eventDatesSet = computed(() => {
    const dates = new Set();
    for (const ev of filteredEvents.value) {
        dates.add(ev.start_at.slice(0, 10));
    }
    return dates;
});

const selectedDayEvents = computed(() => {
    return filteredEvents.value.filter(e => e.start_at.slice(0, 10) === selectedDate.value);
});

function prevMonth() {
    if (calendarMonth.value === 0) {
        calendarMonth.value = 11;
        calendarYear.value--;
    } else {
        calendarMonth.value--;
    }
}

function nextMonth() {
    if (calendarMonth.value === 11) {
        calendarMonth.value = 0;
        calendarYear.value++;
    } else {
        calendarMonth.value++;
    }
}

function goToday() {
    calendarMonth.value = today.getMonth();
    calendarYear.value = today.getFullYear();
    selectedDate.value = today.toISOString().slice(0, 10);
}

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
                        :class="viewMode === 'calendar' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'calendar'"
                        title="Vue calendrier"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    </button>
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
        <!-- CALENDAR VIEW (always shown, even with no events) -->
        <template v-if="viewMode === 'calendar'">
            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Calendar grid (left) -->
                <div class="lg:col-span-3 rounded-xl border border-gray-100 bg-white p-5">
                    <!-- Month nav -->
                    <div class="flex items-center justify-between mb-4">
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600" @click="prevMonth">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                        </button>
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-semibold text-gray-900 capitalize">{{ calendarMonthLabel }}</h3>
                            <button class="rounded-md px-2 py-0.5 text-[10px] font-medium text-emerald-600 hover:bg-emerald-50" @click="goToday">Aujourd'hui</button>
                        </div>
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600" @click="nextMonth">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>

                    <!-- Day headers -->
                    <div class="grid grid-cols-7 mb-1">
                        <div v-for="d in ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']" :key="d" class="py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ d }}</div>
                    </div>

                    <!-- Days grid -->
                    <div class="grid grid-cols-7">
                        <button
                            v-for="day in calendarDays"
                            :key="day.date"
                            class="relative flex flex-col items-center py-2 transition rounded-lg"
                            :class="[
                                day.current ? 'text-gray-900' : 'text-gray-300',
                                selectedDate === day.date ? 'bg-emerald-50' : 'hover:bg-gray-50',
                                day.date === today.toISOString().slice(0, 10) && selectedDate !== day.date ? 'font-bold' : '',
                            ]"
                            @click="selectedDate = day.date"
                        >
                            <span class="text-sm" :class="selectedDate === day.date ? 'flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-white font-semibold' : ''">{{ day.day }}</span>
                            <!-- Event dots -->
                            <div v-if="eventDatesSet.has(day.date)" class="flex gap-0.5 mt-0.5">
                                <span class="h-1 w-1 rounded-full bg-emerald-500" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Day events (right) -->
                <div class="lg:col-span-2 space-y-3">
                    <p class="text-xs font-semibold capitalize tracking-wider text-gray-400">{{ formatDate(selectedDate) }}</p>

                    <div v-if="selectedDayEvents.length" class="space-y-2">
                        <div
                            v-for="ev in selectedDayEvents"
                            :key="ev.id"
                            class="rounded-xl border-l-4 border border-gray-100 bg-white p-4 transition hover:shadow-sm cursor-pointer"
                            :class="typeColorClasses[ev.type] || typeColorClasses.other"
                            @click="$emit('edit', ev)"
                        >
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeBadgeClasses[ev.type] || typeBadgeClasses.other">{{ ev.type_label }}</span>
                                <span class="text-[10px] text-gray-400">{{ ev.team_name }}</span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ ev.title }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                <span>{{ formatTime(ev.start_at) }}<template v-if="ev.end_at"> - {{ formatTime(ev.end_at) }}</template></span>
                                <span v-if="ev.location">{{ ev.location }}</span>
                            </div>
                            <div v-if="attendanceRouteName || convocationRouteName" class="mt-3 flex gap-1.5">
                                <Link
                                    v-if="convocationRouteName"
                                    :href="route(convocationRouteName, ev.id)"
                                    class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-blue-300 hover:text-blue-600"
                                    title="Convocations"
                                    @click.stop
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 4.5 0Z" /></svg>
                                </Link>
                                <Link
                                    v-if="attendanceRouteName"
                                    :href="route(attendanceRouteName, ev.id)"
                                    class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-emerald-300 hover:text-emerald-600"
                                    title="Feuille d'appel"
                                    @click.stop
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-gray-200 p-6 text-center">
                        <p class="text-sm text-gray-400">Aucun événement ce jour.</p>
                    </div>
                </div>
            </div>
        </template>

        <!-- LIST / CARD VIEWS -->
        <template v-else-if="groupedByDate.length">
            <!-- LIST VIEW -->
            <div v-if="viewMode === 'list'" class="space-y-8">
                <section v-for="[date, eventsOfDay] in groupedByDate" :key="date">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ formatDate(date) }}</p>
                    <div class="space-y-2">
                        <div v-for="ev in eventsOfDay" :key="ev.id" class="flex items-center gap-2 rounded-xl border border-gray-100 bg-white transition hover:border-emerald-200 hover:shadow-sm">
                            <button class="flex flex-1 items-center gap-4 p-4 text-left" @click="$emit('edit', ev)">
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
                            </button>
                            <div class="mr-3 flex shrink-0 gap-1.5">
                                <Link
                                    v-if="convocationRouteName"
                                    :href="route(convocationRouteName, ev.id)"
                                    class="rounded-lg border border-gray-200 p-2 text-gray-400 transition hover:border-blue-300 hover:text-blue-600"
                                    title="Convocations"
                                    @click.stop
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                </Link>
                                <Link
                                    v-if="attendanceRouteName"
                                    :href="route(attendanceRouteName, ev.id)"
                                    class="rounded-lg border border-gray-200 p-2 text-gray-400 transition hover:border-emerald-300 hover:text-emerald-600"
                                    title="Feuille d'appel"
                                    @click.stop
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- CARD VIEW -->
            <div v-else-if="viewMode === 'card'" class="space-y-8">
                <section v-for="[date, eventsOfDay] in groupedByDate" :key="date">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ formatDate(date) }}</p>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="ev in eventsOfDay"
                            :key="ev.id"
                            class="flex flex-col rounded-xl border-l-4 border border-gray-100 bg-white p-5 text-left transition hover:shadow-md"
                            :class="typeColorClasses[ev.type] || typeColorClasses.other"
                        >
                            <button class="flex-1 text-left" @click="$emit('edit', ev)">
                                <div class="flex items-start justify-between">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeBadgeClasses[ev.type] || typeBadgeClasses.other">{{ ev.type_label }}</span>
                                    <div class="text-center leading-none">
                                        <p class="text-lg font-bold text-gray-900">{{ formatDay(ev.start_at) }}</p>
                                        <p class="text-[10px] font-medium text-gray-400">{{ formatMonth(ev.start_at) }}</p>
                                    </div>
                                </div>
                                <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ ev.title }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ ev.team_name }}</p>
                            </button>
                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="flex items-center gap-3 text-xs text-gray-400">
                                    <span>{{ formatTime(ev.start_at) }}<template v-if="ev.end_at"> - {{ formatTime(ev.end_at) }}</template></span>
                                    <span v-if="ev.location" class="truncate">{{ ev.location }}</span>
                                </div>
                                <div class="flex shrink-0 gap-1">
                                    <Link
                                        v-if="convocationRouteName"
                                        :href="route(convocationRouteName, ev.id)"
                                        class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-blue-300 hover:text-blue-600"
                                        title="Convocations"
                                        @click.stop
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                    </Link>
                                    <Link
                                        v-if="attendanceRouteName"
                                        :href="route(attendanceRouteName, ev.id)"
                                        class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-emerald-300 hover:text-emerald-600"
                                        title="Feuille d'appel"
                                        @click.stop
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
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

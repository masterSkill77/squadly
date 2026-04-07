<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    dateKey: { type: String, default: 'start_at' },
    viewModes: { type: Array, default: () => ['calendar', 'list', 'card'] },
    defaultView: { type: String, default: 'calendar' },
});

const viewMode = ref(props.defaultView);

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

    let startOffset = (firstDay.getDay() + 6) % 7;
    const days = [];

    for (let i = startOffset - 1; i >= 0; i--) {
        const d = new Date(year, month, -i);
        days.push({ date: d.toISOString().slice(0, 10), day: d.getDate(), current: false });
    }

    for (let i = 1; i <= lastDay.getDate(); i++) {
        const d = new Date(year, month, i);
        days.push({ date: d.toISOString().slice(0, 10), day: i, current: true });
    }

    while (days.length % 7 !== 0) {
        const d = new Date(year, month + 1, days.length - startOffset - lastDay.getDate() + 1);
        days.push({ date: d.toISOString().slice(0, 10), day: d.getDate(), current: false });
    }

    return days;
});

const itemDatesSet = computed(() => {
    const dates = new Set();
    for (const item of props.items) {
        const val = item[props.dateKey];
        if (val) dates.add(val.slice(0, 10));
    }
    return dates;
});

const selectedDayItems = computed(() => {
    return props.items.filter(item => {
        const val = item[props.dateKey];
        return val && val.slice(0, 10) === selectedDate.value;
    });
});

const groupedByDate = computed(() => {
    const groups = {};
    for (const item of props.items) {
        const val = item[props.dateKey];
        if (!val) continue;
        const date = val.slice(0, 10);
        if (!groups[date]) groups[date] = [];
        groups[date].push(item);
    }
    return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b));
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

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

defineExpose({ viewMode, selectedDate });
</script>

<template>
    <div class="space-y-6">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2">
            <slot name="filters" />

            <div class="ml-auto flex items-center gap-2">
                <!-- View toggle -->
                <div class="flex items-center gap-0.5 rounded-lg border border-gray-200 bg-white p-0.5">
                    <button
                        v-if="viewModes.includes('calendar')"
                        class="rounded-md p-1.5 transition"
                        :class="viewMode === 'calendar' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'calendar'"
                        title="Vue calendrier"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    </button>
                    <button
                        v-if="viewModes.includes('list')"
                        class="rounded-md p-1.5 transition"
                        :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'list'"
                        title="Vue liste"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                    </button>
                    <button
                        v-if="viewModes.includes('card')"
                        class="rounded-md p-1.5 transition"
                        :class="viewMode === 'card' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                        @click="viewMode = 'card'"
                        title="Vue cartes"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    </button>
                </div>

                <slot name="actions" />
            </div>
        </div>

        <!-- CALENDAR VIEW -->
        <template v-if="viewMode === 'calendar'">
            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Calendar grid -->
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
                            <slot name="day-indicator" :date="day.date" :has-items="itemDatesSet.has(day.date)">
                                <div v-if="itemDatesSet.has(day.date)" class="flex gap-0.5 mt-0.5">
                                    <span class="h-1 w-1 rounded-full bg-emerald-500" />
                                </div>
                            </slot>
                        </button>
                    </div>
                </div>

                <!-- Selected day items -->
                <div class="lg:col-span-2 space-y-3">
                    <p class="text-xs font-semibold capitalize tracking-wider text-gray-400">{{ formatDate(selectedDate) }}</p>

                    <div v-if="selectedDayItems.length" class="space-y-2">
                        <slot name="day-item" v-for="item in selectedDayItems" :key="item.id" :item="item" />
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-gray-200 p-6 text-center">
                        <slot name="day-empty">
                            <p class="text-sm text-gray-400">Rien ce jour.</p>
                        </slot>
                    </div>
                </div>
            </div>
        </template>

        <!-- LIST / CARD VIEWS -->
        <template v-else-if="groupedByDate.length">
            <div class="space-y-8">
                <section v-for="[date, itemsOfDay] in groupedByDate" :key="date">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ formatDate(date) }}</p>

                    <!-- LIST -->
                    <div v-if="viewMode === 'list'" class="space-y-2">
                        <slot name="list-item" v-for="item in itemsOfDay" :key="item.id" :item="item" />
                    </div>

                    <!-- CARD -->
                    <div v-else-if="viewMode === 'card'" class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <slot name="card-item" v-for="item in itemsOfDay" :key="item.id" :item="item" />
                    </div>
                </section>
            </div>
        </template>

        <!-- Empty state -->
        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <slot name="empty">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                <p class="mt-3 text-sm text-gray-400">Aucun element.</p>
            </slot>
        </div>
    </div>
</template>

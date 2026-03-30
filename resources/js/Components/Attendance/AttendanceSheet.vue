<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    players: Array,
    statuses: Array,
    storeRoute: String,
});

const form = useForm({
    attendances: props.players.map(p => ({
        user_id: p.user_id,
        status: p.status || null,
    })),
});

const statusStyles = {
    present: 'border-emerald-500 bg-emerald-50 text-emerald-700',
    absent: 'border-red-500 bg-red-50 text-red-700',
    justified: 'border-amber-500 bg-amber-50 text-amber-700',
};

const summary = computed(() => {
    const total = form.attendances.length;
    const present = form.attendances.filter(a => a.status === 'present').length;
    const absent = form.attendances.filter(a => a.status === 'absent').length;
    const justified = form.attendances.filter(a => a.status === 'justified').length;
    const unmarked = form.attendances.filter(a => !a.status).length;
    return { total, present, absent, justified, unmarked };
});

function setStatus(index, status) {
    form.attendances[index].status = form.attendances[index].status === status ? null : status;
}

function setAllPresent() {
    form.attendances.forEach(a => { a.status = 'present'; });
}

function submit() {
    form.post(props.storeRoute, { preserveScroll: true });
}
</script>

<template>
    <div>
        <!-- Summary bar -->
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 bg-white p-4">
            <span class="text-sm font-medium text-gray-700">{{ summary.total }} joueur{{ summary.total > 1 ? 's' : '' }}</span>
            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ summary.present }} Présent{{ summary.present > 1 ? 's' : '' }}</span>
            <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">{{ summary.absent }} Absent{{ summary.absent > 1 ? 's' : '' }}</span>
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ summary.justified }} Justifié{{ summary.justified > 1 ? 's' : '' }}</span>
            <span v-if="summary.unmarked" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{{ summary.unmarked }} non marqué{{ summary.unmarked > 1 ? 's' : '' }}</span>

            <button type="button" class="ml-auto text-xs font-medium text-emerald-600 hover:text-emerald-700" @click="setAllPresent">
                Tout présent
            </button>
        </div>

        <!-- Player list -->
        <form class="mt-4" @submit.prevent="submit">
            <div v-if="players.length" class="space-y-1">
                <div
                    v-for="(player, i) in players"
                    :key="player.user_id"
                    class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                    :class="form.attendances[i].status ? 'bg-white' : 'bg-gray-50'"
                >
                    <!-- Avatar -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                        {{ player.first_name.charAt(0) }}{{ player.last_name.charAt(0) }}
                    </div>

                    <!-- Name -->
                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-900">{{ player.first_name }} {{ player.last_name }}</span>

                    <!-- Status toggles -->
                    <div class="flex gap-1.5">
                        <button
                            v-for="s in statuses"
                            :key="s.value"
                            type="button"
                            class="rounded-full border-2 px-2.5 py-1 text-[11px] font-semibold transition"
                            :class="form.attendances[i].status === s.value
                                ? statusStyles[s.value]
                                : 'border-gray-200 text-gray-400 hover:border-gray-300'"
                            @click="setStatus(i, s.value)"
                        >
                            {{ s.label }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">Aucun joueur dans cette équipe.</p>
            </div>

            <div v-if="players.length" class="mt-6 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing || summary.unmarked === summary.total"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                >
                    Enregistrer les présences
                </button>
            </div>
        </form>
    </div>
</template>

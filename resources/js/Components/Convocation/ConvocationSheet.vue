<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ players: Array, storeRoute: String });

const form = useForm({
    user_ids: props.players.filter(p => p.is_convoked).map(p => p.user_id),
});

const summary = computed(() => {
    const convoked = form.user_ids.length;
    return { convoked, total: props.players.length };
});

const statusBadge = {
    pending: 'bg-gray-100 text-gray-600',
    confirmed: 'bg-emerald-50 text-emerald-700',
    declined: 'bg-red-50 text-red-700',
};

function toggle(userId) {
    const idx = form.user_ids.indexOf(userId);
    if (idx === -1) {
        form.user_ids.push(userId);
    } else {
        form.user_ids.splice(idx, 1);
    }
}

function selectAll() {
    if (form.user_ids.length === props.players.length) {
        form.user_ids = [];
    } else {
        form.user_ids = props.players.map(p => p.user_id);
    }
}

function submit() {
    form.post(props.storeRoute, { preserveScroll: true });
}
</script>

<template>
    <div>
        <!-- Summary -->
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 bg-white p-4">
            <span class="text-sm font-medium text-gray-700">{{ summary.total }} joueur{{ summary.total > 1 ? 's' : '' }}</span>
            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ summary.convoked }} convoqué{{ summary.convoked > 1 ? 's' : '' }}</span>

            <button type="button" class="ml-auto text-xs font-medium text-emerald-600 hover:text-emerald-700" @click="selectAll">
                {{ form.user_ids.length === players.length ? 'Tout désélectionner' : 'Tout sélectionner' }}
            </button>
        </div>

        <!-- Player list -->
        <form class="mt-4" @submit.prevent="submit">
            <div v-if="players.length" class="space-y-1">
                <button
                    v-for="player in players"
                    :key="player.user_id"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left transition"
                    :class="form.user_ids.includes(player.user_id) ? 'bg-emerald-50/50' : 'bg-white hover:bg-gray-50'"
                    @click="toggle(player.user_id)"
                >
                    <!-- Checkbox -->
                    <div
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition"
                        :class="form.user_ids.includes(player.user_id) ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'"
                    >
                        <svg v-if="form.user_ids.includes(player.user_id)" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>

                    <!-- Avatar -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                        {{ player.first_name.charAt(0) }}{{ player.last_name.charAt(0) }}
                    </div>

                    <!-- Name -->
                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-900">{{ player.first_name }} {{ player.last_name }}</span>

                    <!-- Status badge (if already convoked) -->
                    <span
                        v-if="player.is_convoked && player.status_label"
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="statusBadge[player.status] || statusBadge.pending"
                    >
                        {{ player.status_label }}
                    </span>
                </button>
            </div>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-400">Aucun joueur dans cette équipe.</p>
            </div>

            <div v-if="players.length" class="mt-6 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                >
                    Enregistrer les convocations
                </button>
            </div>
        </form>
    </div>
</template>

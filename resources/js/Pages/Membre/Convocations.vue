<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({ convocations: Array });

const statusBadge = {
    pending: 'bg-gray-100 text-gray-600',
    confirmed: 'bg-emerald-50 text-emerald-700',
    declined: 'bg-red-50 text-red-700',
};

function respond(id, status) {
    router.put(route('membre.convocations.respond', id), { status }, { preserveScroll: true });
}

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Mes convocations" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Mes convocations</h2>
        </template>

        <div class="space-y-3" v-if="convocations.length">
            <div
                v-for="c in convocations"
                :key="c.id"
                class="rounded-xl border border-gray-100 bg-white p-5"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">{{ c.event_type_label }}</span>
                            <span class="text-[10px] text-gray-400">{{ c.team_name }}</span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ c.event_title }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(c.event_start_at) }}</p>
                        <p v-if="c.event_location" class="text-xs text-gray-400">{{ c.event_location }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="statusBadge[c.status]"
                        >
                            {{ c.status_label }}
                        </span>

                        <template v-if="c.status === 'pending'">
                            <button
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                @click="respond(c.id, 'confirmed')"
                            >
                                Confirmer
                            </button>
                            <button
                                class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                @click="respond(c.id, 'declined')"
                            >
                                Décliner
                            </button>
                        </template>

                        <template v-else>
                            <button
                                v-if="c.status === 'declined'"
                                class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                                @click="respond(c.id, 'confirmed')"
                            >
                                Changer en confirmé
                            </button>
                            <button
                                v-if="c.status === 'confirmed'"
                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-50"
                                @click="respond(c.id, 'declined')"
                            >
                                Décliner
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <p class="text-sm text-gray-400">Aucune convocation à venir.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    notifications: Array,
});

function notificationIcon(type) {
    switch (type) {
        case 'ConvocationReceived': return { icon: '🏟️', bg: 'bg-blue-50' };
        case 'AnnouncementPublished': return { icon: '📢', bg: 'bg-purple-50' };
        case 'MemberInvited': return { icon: '👋', bg: 'bg-emerald-50' };
        default: return { icon: '🔔', bg: 'bg-gray-50' };
    }
}

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

async function markAsRead(id) {
    await window.axios.post(route('notifications.read', id));
    router.reload();
}

async function markAllAsRead() {
    await window.axios.post(route('notifications.read-all'));
    router.reload();
}
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
        </template>

        <div class="mx-auto max-w-3xl">
            <!-- Header actions -->
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    {{ notifications.length }} notification{{ notifications.length !== 1 ? 's' : '' }}
                </p>
                <button
                    v-if="notifications.some(n => !n.read_at)"
                    class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                    @click="markAllAsRead"
                >
                    Tout marquer comme lu
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="notifications.length === 0" class="rounded-xl border border-gray-100 bg-white py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <p class="mt-3 text-sm text-gray-500">Aucune notification</p>
            </div>

            <!-- Notification list -->
            <div v-else class="divide-y divide-gray-100 rounded-xl border border-gray-100 bg-white">
                <div
                    v-for="notif in notifications"
                    :key="notif.id"
                    class="flex items-start gap-4 px-5 py-4 transition"
                    :class="notif.read_at ? 'opacity-60' : 'bg-emerald-50/30'"
                >
                    <span
                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg"
                        :class="notificationIcon(notif.type).bg"
                    >
                        {{ notificationIcon(notif.type).icon }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-800">{{ notif.data.message }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ formatDate(notif.created_at) }}</p>
                    </div>
                    <button
                        v-if="!notif.read_at"
                        class="mt-1 shrink-0 rounded-lg p-1.5 text-gray-300 transition hover:bg-gray-100 hover:text-emerald-600"
                        title="Marquer comme lu"
                        @click="markAsRead(notif.id)"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const open = ref(false);
const notifications = ref([]);
const loading = ref(false);

const unreadCount = computed(() => page.props.auth.unread_notifications_count ?? 0);

let pollInterval = null;

async function fetchNotifications() {
    if (loading.value) return;
    loading.value = true;
    try {
        const { data } = await window.axios.get(route('notifications.unread'));
        notifications.value = data.notifications;
    } finally {
        loading.value = false;
    }
}

function toggle() {
    open.value = !open.value;
    if (open.value) {
        fetchNotifications();
    }
}

function close() {
    open.value = false;
}

async function markAsRead(id) {
    await window.axios.post(route('notifications.read', id));
    notifications.value = notifications.value.filter(n => n.id !== id);
    router.reload({ only: ['auth'] });
}

async function markAllAsRead() {
    await window.axios.post(route('notifications.read-all'));
    notifications.value = [];
    router.reload({ only: ['auth'] });
}

function goToAll() {
    close();
    router.visit(route('notifications.index'));
}

function notificationIcon(type) {
    switch (type) {
        case 'ConvocationReceived': return '🏟️';
        case 'AnnouncementPublished': return '📢';
        case 'MemberInvited': return '👋';
        default: return '🔔';
    }
}

function timeAgo(dateStr) {
    const now = new Date();
    const date = new Date(dateStr);
    const seconds = Math.floor((now - date) / 1000);
    if (seconds < 60) return "À l'instant";
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `Il y a ${minutes} min`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `Il y a ${hours}h`;
    const days = Math.floor(hours / 24);
    return `Il y a ${days}j`;
}

// Close dropdown when clicking outside
function handleClickOutside(e) {
    const el = document.getElementById('notification-bell');
    if (el && !el.contains(e.target)) {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Poll every 30 seconds
    pollInterval = setInterval(() => {
        if (!open.value) router.reload({ only: ['auth'] });
    }, 30000);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div id="notification-bell" class="relative">
        <button
            class="relative rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
            title="Notifications"
            @click="toggle"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-95 opacity-0"
        >
            <div
                v-if="open"
                class="absolute right-0 top-full mt-2 w-80 rounded-xl border border-gray-100 bg-white shadow-lg ring-1 ring-black/5"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    <button
                        v-if="notifications.length > 0"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700"
                        @click="markAllAsRead"
                    >
                        Tout marquer comme lu
                    </button>
                </div>

                <!-- List -->
                <div class="max-h-80 overflow-y-auto">
                    <div v-if="loading && notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                        Chargement…
                    </div>
                    <div v-else-if="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                        Aucune nouvelle notification
                    </div>
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="flex items-start gap-3 border-b border-gray-50 px-4 py-3 transition hover:bg-gray-50"
                    >
                        <span class="mt-0.5 text-lg">{{ notificationIcon(notif.type) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700">{{ notif.data.message }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ timeAgo(notif.created_at) }}</p>
                        </div>
                        <button
                            class="mt-1 shrink-0 rounded p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500"
                            title="Marquer comme lu"
                            @click="markAsRead(notif.id)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-100 px-4 py-2">
                    <button
                        class="w-full rounded-lg py-1.5 text-center text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                        @click="goToAll"
                    >
                        Voir toutes les notifications
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

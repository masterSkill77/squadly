<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AnnouncementModal from '@/Components/Announcements/AnnouncementModal.vue';

const props = defineProps({
    announcements: Array,
    teams: Array,
    sections: Array,
    canCreate: Boolean,
    isAdmin: Boolean,
});

const showModal = ref(false);

const targetBadge = {
    club: 'bg-emerald-50 text-emerald-700',
    section: 'bg-purple-50 text-purple-700',
    teams: 'bg-blue-50 text-blue-700',
};

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / 1048576).toFixed(1) + ' Mo';
}

function deleteAnnouncement(id) {
    if (confirm('Supprimer cette annonce ?')) {
        router.delete(route('announcements.destroy', id));
    }
}
</script>

<template>
    <Head title="Annonces" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Annonces</h2>
        </template>

        <div class="space-y-6">
            <!-- Add button -->
            <div v-if="canCreate" class="flex justify-end">
                <button
                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="showModal = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nouvelle annonce
                </button>
            </div>

            <!-- Announcements feed -->
            <div v-if="announcements.length" class="space-y-4">
                <div
                    v-for="a in announcements"
                    :key="a.id"
                    class="rounded-xl border border-gray-100 bg-white p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="targetBadge[a.target_type] || targetBadge.club"
                                >
                                    {{ a.target_label }}
                                </span>
                                <span class="text-[10px] text-gray-400">{{ formatDate(a.created_at) }}</span>
                            </div>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ a.title }}</h3>
                            <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ a.content }}</p>

                            <!-- Attachments -->
                            <div v-if="a.attachments?.length" class="mt-3">
                                <!-- Image gallery -->
                                <div v-if="a.attachments.some(att => att.is_image)" class="flex flex-wrap gap-2">
                                    <a
                                        v-for="img in a.attachments.filter(att => att.is_image)"
                                        :key="img.id"
                                        :href="img.url"
                                        target="_blank"
                                        class="group overflow-hidden rounded-lg border border-gray-100"
                                    >
                                        <img :src="img.url" :alt="img.name" class="h-32 w-auto object-cover transition group-hover:scale-105" />
                                    </a>
                                </div>
                                <!-- File list -->
                                <div v-if="a.attachments.some(att => !att.is_image)" class="mt-2 flex flex-wrap gap-2">
                                    <a
                                        v-for="file in a.attachments.filter(att => !att.is_image)"
                                        :key="file.id"
                                        :href="file.url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                        {{ file.name }}
                                        <span class="text-gray-400">({{ formatSize(file.size) }})</span>
                                    </a>
                                </div>
                            </div>

                            <p class="mt-3 text-xs text-gray-400">Par {{ a.author_name }}</p>
                        </div>
                        <button
                            v-if="a.can_delete"
                            class="shrink-0 rounded-lg p-1.5 text-gray-300 transition hover:bg-red-50 hover:text-red-500"
                            title="Supprimer"
                            @click="deleteAnnouncement(a.id)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a.75.75 0 0 1-.999-.272 17.957 17.957 0 0 1-1.41-2.992M10.34 15.84a21.174 21.174 0 0 0 0-7.68m0 7.68a22.39 22.39 0 0 1-1.453 4.235M10.34 8.16c.388-.948.852-1.856 1.381-2.717a.75.75 0 0 1 .999-.272l.657.38c.524.3.71.96.463 1.511-.273.617-.508 1.254-.705 1.905M14.084 8.16c.688.06 1.386.09 2.09.09h.75a4.5 4.5 0 0 1 0 9h-.75c-.704 0-1.402.03-2.09.09" /></svg>
                <p class="mt-3 text-sm text-gray-400">Aucune annonce pour le moment.</p>
                <button v-if="canCreate" class="mt-2 text-sm font-medium text-emerald-600 hover:text-emerald-700" @click="showModal = true">
                    Publier la première annonce
                </button>
            </div>
        </div>

        <AnnouncementModal
            :show="showModal"
            :teams="teams"
            :sections="sections"
            :is-admin="isAdmin"
            @close="showModal = false"
        />
    </AuthenticatedLayout>
</template>

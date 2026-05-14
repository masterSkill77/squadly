<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    competition: Object,
    announcements: Array,
});

const showForm = ref(false);

const form = useForm({
    title: '',
    content: '',
});

const submit = () => {
    form.post(route('organizer.competitions.announcements.store', props.competition.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
};

const deleteAnnouncement = (id) => {
    if (!confirm('Supprimer cette annonce ?')) return;
    router.delete(route('organizer.competitions.announcements.destroy', [props.competition.id, id]), {
        preserveScroll: true,
    });
};

const formatDate = (iso) => {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Annonces" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('organizer.competitions.show', competition.id)"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Annonces</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Action bar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    {{ announcements.length }} annonce(s) publiée(s)
                </p>
                <button
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="showForm = !showForm"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouvelle annonce
                </button>
            </div>

            <!-- Create form -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showForm" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Nouvelle annonce</h3>
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700">Titre</label>
                            <input
                                v-model="form.title"
                                type="text"
                                maxlength="255"
                                placeholder="Titre de l'annonce"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                :class="{ 'border-red-400': form.errors.title }"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700">Contenu</label>
                            <textarea
                                v-model="form.content"
                                rows="5"
                                maxlength="5000"
                                placeholder="Message de l'annonce…"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                :class="{ 'border-red-400': form.errors.content }"
                            />
                            <div class="mt-1 flex items-center justify-between">
                                <p v-if="form.errors.content" class="text-xs text-red-600">{{ form.errors.content }}</p>
                                <p class="ml-auto text-xs text-gray-400">{{ form.content.length }}/5000</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="button"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
                                @click="showForm = false; form.reset()"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60"
                            >
                                Publier
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>

            <!-- Announcements list -->
            <div v-if="announcements.length" class="space-y-4">
                <div
                    v-for="announcement in announcements"
                    :key="announcement.id"
                    class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900">{{ announcement.title }}</h4>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Par {{ announcement.author_name }} &middot; {{ formatDate(announcement.created_at) }}
                            </p>
                        </div>
                        <button
                            v-if="announcement.can_delete"
                            class="shrink-0 rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                            title="Supprimer"
                            @click="deleteAnnouncement(announcement.id)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-3 whitespace-pre-wrap text-sm text-gray-700">{{ announcement.content }}</p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-dashed border-gray-300 p-12 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 0 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                </svg>
                <p class="mt-3 text-sm text-gray-500">Aucune annonce publiée.</p>
                <p class="mt-1 text-xs text-gray-400">Les annonces sont visibles uniquement par les clubs approuvés dans cette compétition.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

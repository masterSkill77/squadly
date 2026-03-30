<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    teams: Array,
    sections: Array,
    isAdmin: Boolean,
});
const emit = defineEmits(['close']);

const fileInput = ref(null);

const form = useForm({
    title: '',
    content: '',
    target_type: props.isAdmin ? 'club' : 'teams',
    section_id: '',
    team_ids: [],
    attachments: [],
});

const filePreviews = ref([]);

function toggleTeam(id) {
    const idx = form.team_ids.indexOf(id);
    if (idx === -1) {
        form.team_ids.push(id);
    } else {
        form.team_ids.splice(idx, 1);
    }
}

function selectAllTeams() {
    if (form.team_ids.length === props.teams.length) {
        form.team_ids = [];
    } else {
        form.team_ids = props.teams.map(t => t.id);
    }
}

function handleFiles(e) {
    const newFiles = Array.from(e.target.files);
    const current = form.attachments;
    const combined = [...current, ...newFiles].slice(0, 5);
    form.attachments = combined;

    // Generate previews
    filePreviews.value = combined.map(file => ({
        name: file.name,
        size: file.size,
        is_image: file.type.startsWith('image/'),
        url: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
    }));

    // Reset input so same file can be re-added
    if (fileInput.value) fileInput.value.value = '';
}

function removeFile(index) {
    if (filePreviews.value[index]?.url) {
        URL.revokeObjectURL(filePreviews.value[index].url);
    }
    form.attachments.splice(index, 1);
    filePreviews.value.splice(index, 1);
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / 1048576).toFixed(1) + ' Mo';
}

function fileIcon(name) {
    const ext = name.split('.').pop().toLowerCase();
    if (['pdf'].includes(ext)) return 'pdf';
    if (['doc', 'docx'].includes(ext)) return 'doc';
    if (['xls', 'xlsx'].includes(ext)) return 'xls';
    return 'file';
}

function submit() {
    form.post(route('announcements.store'), {
        forceFormData: true,
        onSuccess: () => {
            filePreviews.value.forEach(p => { if (p.url) URL.revokeObjectURL(p.url); });
            filePreviews.value = [];
            form.reset();
            form.target_type = props.isAdmin ? 'club' : 'teams';
            emit('close');
        },
    });
}
</script>

<template>
    <Modal :show="show" max-width="lg" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Nouvelle annonce</h3>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <!-- Target type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Destinataires</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-if="isAdmin"
                            type="button"
                            class="rounded-full border-2 px-3.5 py-1.5 text-xs font-medium transition"
                            :class="form.target_type === 'club' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            @click="form.target_type = 'club'"
                        >
                            Tout le club
                        </button>
                        <button
                            v-if="isAdmin && sections.length"
                            type="button"
                            class="rounded-full border-2 px-3.5 py-1.5 text-xs font-medium transition"
                            :class="form.target_type === 'section' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            @click="form.target_type = 'section'"
                        >
                            Une section
                        </button>
                        <button
                            type="button"
                            class="rounded-full border-2 px-3.5 py-1.5 text-xs font-medium transition"
                            :class="form.target_type === 'teams' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            @click="form.target_type = 'teams'"
                        >
                            Équipe(s)
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.target_type" />
                </div>

                <!-- Section select -->
                <div v-if="form.target_type === 'section'">
                    <label class="block text-sm font-medium text-gray-700">Section</label>
                    <select v-model="form.section_id" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="" disabled>Choisir une section</option>
                        <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.sport_type }}</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.section_id" />
                </div>

                <!-- Teams multi-select -->
                <div v-if="form.target_type === 'teams'">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Équipes</label>
                        <button type="button" class="text-xs text-emerald-600 hover:text-emerald-700" @click="selectAllTeams">
                            {{ form.team_ids.length === teams.length ? 'Tout désélectionner' : 'Tout sélectionner' }}
                        </button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="t in teams"
                            :key="t.id"
                            type="button"
                            class="rounded-full border-2 px-3 py-1 text-xs font-medium transition"
                            :class="form.team_ids.includes(t.id)
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            @click="toggleTeam(t.id)"
                        >
                            {{ t.name }}
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.team_ids" />
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Titre</label>
                    <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Changement d'horaire, Info importante…" />
                    <InputError class="mt-1" :message="form.errors.title" />
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea v-model="form.content" rows="4" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Votre message…" />
                    <InputError class="mt-1" :message="form.errors.content" />
                </div>

                <!-- Attachments -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pièces jointes</label>
                    <div class="mt-2">
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-200 px-4 py-3 transition hover:border-emerald-400 hover:bg-emerald-50/50"
                            :class="form.attachments.length >= 5 ? 'pointer-events-none opacity-40' : ''"
                        >
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                            </svg>
                            <span class="text-sm text-gray-500">
                                {{ form.attachments.length >= 5 ? 'Maximum 5 fichiers' : 'Ajouter des fichiers (images, PDF, documents…)' }}
                            </span>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                class="hidden"
                                :disabled="form.attachments.length >= 5"
                                @input="handleFiles"
                            />
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Max 10 Mo par fichier, 5 fichiers maximum</p>
                        <InputError class="mt-1" :message="form.errors.attachments" />
                    </div>

                    <!-- File previews -->
                    <div v-if="filePreviews.length" class="mt-3 space-y-2">
                        <div
                            v-for="(file, i) in filePreviews"
                            :key="i"
                            class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-2.5"
                        >
                            <!-- Image thumbnail -->
                            <img
                                v-if="file.is_image && file.url"
                                :src="file.url"
                                class="h-10 w-10 shrink-0 rounded-md object-cover"
                                alt=""
                            />
                            <!-- File icon -->
                            <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-white">
                                <span class="text-[10px] font-bold uppercase" :class="{
                                    'text-red-500': fileIcon(file.name) === 'pdf',
                                    'text-blue-500': fileIcon(file.name) === 'doc',
                                    'text-green-600': fileIcon(file.name) === 'xls',
                                    'text-gray-400': fileIcon(file.name) === 'file',
                                }">{{ file.name.split('.').pop() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm text-gray-700">{{ file.name }}</p>
                                <p class="text-xs text-gray-400">{{ formatSize(file.size) }}</p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded p-1 text-gray-300 transition hover:bg-red-50 hover:text-red-500"
                                @click="removeFile(i)"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                    <button
                        type="submit"
                        :disabled="form.processing || (form.target_type === 'teams' && !form.team_ids.length)"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                    >
                        Publier
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

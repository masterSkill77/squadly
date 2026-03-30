<script setup>
import { computed } from 'vue';
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

const form = useForm({
    title: '',
    content: '',
    target_type: props.isAdmin ? 'club' : 'teams',
    section_id: '',
    team_ids: [],
});

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

function submit() {
    form.post(route('announcements.store'), {
        onSuccess: () => { form.reset(); form.target_type = props.isAdmin ? 'club' : 'teams'; emit('close'); },
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

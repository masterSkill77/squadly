<script setup>
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    teams: Array,
    eventTypes: Array,
    event: { type: Object, default: null },
});
const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.event);

const form = useForm({
    team_ids: [],
    team_id: '',
    type: 'training',
    custom_type: '',
    title: '',
    location: '',
    start_at: '',
    end_at: '',
    description: '',
});

watch(() => props.event, (ev) => {
    if (ev) {
        form.team_id = ev.team_id;
        form.team_ids = [];
        form.type = ev.type;
        form.custom_type = ev.custom_type || '';
        form.title = ev.title;
        form.location = ev.location || '';
        form.start_at = ev.start_at ? ev.start_at.slice(0, 16) : '';
        form.end_at = ev.end_at ? ev.end_at.slice(0, 16) : '';
        form.description = ev.description || '';
    } else {
        form.reset();
        form.type = 'training';
    }
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
    if (isEditing.value) {
        form.transform((data) => ({
            type: data.type,
            custom_type: data.custom_type,
            title: data.title,
            location: data.location,
            start_at: data.start_at,
            end_at: data.end_at,
            description: data.description,
        })).put(route('events.update', props.event.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.transform((data) => ({
            team_ids: data.team_ids,
            type: data.type,
            custom_type: data.custom_type,
            title: data.title,
            location: data.location,
            start_at: data.start_at,
            end_at: data.end_at,
            description: data.description,
        })).post(route('events.store'), {
            onSuccess: () => { form.reset(); form.type = 'training'; emit('close'); },
        });
    }
}

function deleteEvent() {
    if (confirm('Supprimer cet événement ?')) {
        router.delete(route('events.destroy', props.event.id), {
            onSuccess: () => emit('close'),
        });
    }
}
</script>

<template>
    <Modal :show="show" max-width="lg" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ isEditing ? 'Modifier l\'événement' : 'Nouvel événement' }}
            </h3>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <!-- Create: multi-select teams -->
                <div v-if="!isEditing">
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

                <!-- Edit: show team name (read-only) -->
                <div v-if="isEditing" class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Équipe :</span>
                    <span class="text-sm font-medium text-gray-900">{{ event.team_name }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="et in eventTypes"
                            :key="et.value"
                            type="button"
                            class="rounded-full border-2 px-3.5 py-1.5 text-xs font-medium transition"
                            :class="form.type === et.value
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            @click="form.type = et.value"
                        >
                            {{ et.label }}
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.type" />
                </div>

                <div v-if="form.type === 'other'">
                    <label class="block text-sm font-medium text-gray-700">Nom du type</label>
                    <input v-model="form.custom_type" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Tournoi, Stage, Réunion…" />
                    <InputError class="mt-1" :message="form.errors.custom_type" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Titre</label>
                    <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Entraînement du mardi" />
                    <InputError class="mt-1" :message="form.errors.title" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu</label>
                    <input v-model="form.location" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Stade municipal" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Début</label>
                        <input v-model="form.start_at" type="datetime-local" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.start_at" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fin</label>
                        <input v-model="form.end_at" type="datetime-local" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.end_at" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Notes, consignes…" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button v-if="isEditing" type="button" class="text-sm font-medium text-red-500 hover:text-red-700" @click="deleteEvent">
                        Supprimer
                    </button>
                    <span v-else />
                    <div class="flex gap-3">
                        <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                        <button type="submit" :disabled="form.processing || (!isEditing && !form.team_ids.length)" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">
                            {{ isEditing ? 'Enregistrer' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>

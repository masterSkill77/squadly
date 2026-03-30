<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { sportLabels } from '@/Utils/sportLabels';
import { useForm } from '@inertiajs/vue3';
import { Role } from '@/Utils/roles';

const props = defineProps({ show: Boolean, sections: Array });
const emit = defineEmits(['close']);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    birth_date: '',
    role: Role.Membre,
    team_ids: [],
    sport_profiles: {},
});

const selectedSectionIds = computed(() => {
    if (!props.sections) return [];
    const ids = new Set();
    for (const section of props.sections) {
        for (const team of section.teams) {
            if (form.team_ids.includes(team.id)) {
                ids.add(section.id);
            }
        }
    }
    return [...ids];
});

function toggleTeam(teamId) {
    const idx = form.team_ids.indexOf(teamId);
    if (idx === -1) {
        form.team_ids.push(teamId);
    } else {
        form.team_ids.splice(idx, 1);
    }
}

function ensureSportProfile(sectionId) {
    if (!form.sport_profiles[sectionId]) {
        form.sport_profiles[sectionId] = {};
    }
}

function submit() {
    const sportProfiles = [];
    for (const sectionId of selectedSectionIds.value) {
        const profile = form.sport_profiles[sectionId];
        if (profile && Object.values(profile).some(v => v !== '' && v !== null && v !== undefined)) {
            sportProfiles.push({ section_id: sectionId, sport_profile: profile });
        }
    }

    form.transform((data) => ({
        ...data,
        sport_profiles: sportProfiles.length ? sportProfiles : null,
    })).post(route('members.store'), {
        onSuccess: () => {
            form.reset();
            form.team_ids = [];
            form.sport_profiles = {};
            emit('close');
        },
    });
}
</script>

<template>
    <Modal :show="show" max-width="lg" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Ajouter un membre</h3>
            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <!-- Role selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <div class="mt-2 flex gap-3">
                        <button
                            type="button"
                            class="flex-1 rounded-lg border-2 px-4 py-2.5 text-center text-sm font-medium transition"
                            :class="form.role === Role.Membre ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                            @click="form.role = Role.Membre"
                        >
                            Joueur / Membre
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-lg border-2 px-4 py-2.5 text-center text-sm font-medium transition"
                            :class="form.role === Role.Coach ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                            @click="form.role = Role.Coach"
                        >
                            Coach
                        </button>
                    </div>
                </div>

                <!-- Identity -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prenom</label>
                        <input v-model="form.first_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.first_name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input v-model="form.last_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <InputError class="mt-1" :message="form.errors.last_name" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telephone</label>
                        <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                        <input v-model="form.birth_date" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                </div>

                <!-- Team assignment -->
                <div v-if="sections?.length">
                    <label class="block text-sm font-medium text-gray-700">Equipes</label>
                    <div class="mt-2 space-y-3">
                        <div v-for="section in sections" :key="section.id">
                            <p class="text-xs font-semibold text-gray-500">{{ sportLabels[section.sport_type] || section.sport_type }}</p>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <button
                                    v-for="team in section.teams"
                                    :key="team.id"
                                    type="button"
                                    class="rounded-full border px-3 py-1 text-xs font-medium transition"
                                    :class="form.team_ids.includes(team.id)
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                                    @click="toggleTeam(team.id)"
                                >
                                    {{ team.name }}
                                    <span v-if="team.age_category" class="ml-1 text-[10px] opacity-60">{{ team.age_category }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <InputError class="mt-1" :message="form.errors.team_ids" />
                </div>

                <!-- Sport profiles for selected sections (only for membres) -->
                <template v-if="form.role === Role.Membre && selectedSectionIds.length">
                    <div
                        v-for="section in sections.filter(s => selectedSectionIds.includes(s.id) && s.sport_template?.fields?.length)"
                        :key="'sp-' + section.id"
                        class="rounded-lg border border-gray-100 bg-gray-50 p-4"
                    >
                        <p class="text-sm font-semibold text-gray-700">{{ sportLabels[section.sport_type] || section.sport_type }} — Profil sportif</p>
                        <div class="mt-3 grid grid-cols-2 gap-3" @vue:mounted="ensureSportProfile(section.id)">
                            <div v-for="field in section.sport_template.fields" :key="field.key">
                                <label class="block text-xs font-medium text-gray-600">{{ field.label }}</label>
                                <select
                                    v-if="field.type === 'select'"
                                    :value="form.sport_profiles[section.id]?.[field.key] || ''"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    @change="ensureSportProfile(section.id); form.sport_profiles[section.id][field.key] = $event.target.value"
                                >
                                    <option value="">—</option>
                                    <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                                <input
                                    v-else
                                    :value="form.sport_profiles[section.id]?.[field.key] || ''"
                                    :type="field.type || 'text'"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    @input="ensureSportProfile(section.id); form.sport_profiles[section.id][field.key] = $event.target.value"
                                />
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Ajouter</button>
                </div>
            </form>
        </div>
    </Modal>
</template>

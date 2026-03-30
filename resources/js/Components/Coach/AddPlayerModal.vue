<script setup>
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ show: Boolean, teamId: Number, sportTemplate: Object });
const emit = defineEmits(['close']);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    birth_date: '',
    sport_profile: {},
});

function submit() {
    const hasProfile = Object.values(form.sport_profile).some(v => v !== '' && v !== null && v !== undefined);
    form.transform((data) => ({
        ...data,
        sport_profile: hasProfile ? data.sport_profile : null,
    })).post(route('coach.team.add-player', props.teamId), {
        onSuccess: () => { form.reset(); form.sport_profile = {}; emit('close'); },
    });
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Ajouter un joueur</h3>
            <p class="mt-1 text-sm text-gray-500">Le joueur sera ajouté au club et assigné à cette équipe.</p>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prénom</label>
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
                        <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                        <input v-model="form.birth_date" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                </div>

                <!-- Sport profile fields -->
                <div v-if="sportTemplate?.fields?.length" class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-700">Profil sportif</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div v-for="field in sportTemplate.fields" :key="field.key">
                            <label class="block text-xs font-medium text-gray-600">{{ field.label }}</label>
                            <select
                                v-if="field.type === 'select'"
                                v-model="form.sport_profile[field.key]"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">—</option>
                                <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <input
                                v-else
                                v-model="form.sport_profile[field.key]"
                                :type="field.type || 'text'"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Ajouter</button>
                </div>
            </form>
        </div>
    </Modal>
</template>

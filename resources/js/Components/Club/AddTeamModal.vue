<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({ show: Boolean, section: Object });
const emit = defineEmits(['close']);

const form = useForm({ section_id: '', name: '', age_category: '' });

watch(() => props.section, (s) => { if (s) form.section_id = s.id; });

function submit() {
    form.post(route('teams.store'), {
        onSuccess: () => { form.reset(); emit('close'); },
    });
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Nouvelle équipe</h3>
            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input v-model="form.name" type="text" required placeholder="Ex : Seniors A" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catégorie d'âge</label>
                    <input v-model="form.age_category" type="text" placeholder="Ex : U17, Senior…" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                    <button type="submit" :disabled="!form.name || form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

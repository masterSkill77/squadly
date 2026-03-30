<script setup>
import Modal from '@/Components/Modal.vue';
import { sportLabels } from '@/Utils/sportLabels';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    availableSports: Array,
});
const emit = defineEmits(['close']);

const form = useForm({ sport_type: '' });

function submit() {
    form.post(route('sections.store'), {
        onSuccess: () => { form.reset(); emit('close'); },
    });
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Ajouter un sport</h3>
            <p class="mt-1 text-sm text-gray-500">Choisissez un sport à ajouter à votre club.</p>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button
                    v-for="sport in availableSports"
                    :key="sport"
                    type="button"
                    class="rounded-xl border-2 px-4 py-3 text-left text-sm font-medium transition"
                    :class="form.sport_type === sport ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                    @click="form.sport_type = sport"
                >
                    {{ sportLabels[sport] || sport }}
                </button>
            </div>

            <p v-if="!availableSports.length" class="mt-4 text-sm text-gray-400">Tous les sports disponibles sont déjà ajoutés.</p>

            <div class="mt-6 flex justify-end gap-3">
                <button class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                <button
                    :disabled="!form.sport_type || form.processing"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                    @click="submit"
                >
                    Ajouter
                </button>
            </div>
        </div>
    </Modal>
</template>

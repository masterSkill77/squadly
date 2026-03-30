<script setup>
import Modal from '@/Components/Modal.vue';

defineProps({ show: Boolean, message: String, processing: Boolean });
defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
            <p class="mt-2 text-sm text-gray-500">{{ message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button class="text-sm text-gray-500 hover:text-gray-700" @click="$emit('close')">Annuler</button>
                <button :disabled="processing" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50" @click="$emit('confirm')">
                    Supprimer
                </button>
            </div>
        </div>
    </Modal>
</template>

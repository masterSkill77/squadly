<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ memberId: Number, userId: Number, documents: Array, missingTypes: Array, currentSeason: String });

const statusStyles = {
    valid: 'bg-emerald-50 text-emerald-700',
    expired: 'bg-red-50 text-red-700',
    missing: 'bg-amber-50 text-amber-700',
};

const form = useForm({
    user_id: props.userId,
    type: '',
    custom_label: '',
    file: null,
    season: props.currentSeason,
    expires_at: '',
    notes: '',
});

function uploadFor(type) {
    form.type = type;
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.pdf,.jpg,.jpeg,.png';
    input.onchange = (e) => {
        form.file = e.target.files[0];
        form.post(route('documents.store'), { forceFormData: true, onSuccess: () => { form.file = null; } });
    };
    input.click();
}
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <h4 class="text-sm font-semibold text-gray-900">Documents</h4>

        <div class="mt-4 space-y-2">
            <div v-for="doc in documents" :key="doc.type + (doc.id || '')" class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-700">{{ doc.type_label }}</p>
                    <p v-if="doc.expires_at" class="text-[10px] text-gray-400">Expire le {{ doc.expires_at }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusStyles[doc.status]">{{ doc.status_label }}</span>
                    <a v-if="doc.file_url" :href="doc.file_url" target="_blank" class="text-gray-400 hover:text-emerald-600" title="Voir">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </a>
                    <button v-if="doc.status === 'missing' || doc.status === 'expired'" class="text-xs text-emerald-600 hover:text-emerald-700" @click="uploadFor(doc.type)">
                        {{ doc.status === 'missing' ? 'Ajouter' : 'Remplacer' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="!documents.length" class="mt-3 text-sm text-gray-400">Aucun document requis.</div>
    </div>
</template>

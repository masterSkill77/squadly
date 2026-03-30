<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ club: Object });

const editing = ref(false);
const logoPreview = ref(null);

const form = useForm({
    name: props.club.name,
    city: props.club.city || '',
    logo: null,
});

function handleLogo(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
}

function save() {
    form.post(route('club.update'), {
        forceFormData: true,
        onSuccess: () => {
            editing.value = false;
            if (logoPreview.value) {
                URL.revokeObjectURL(logoPreview.value);
                logoPreview.value = null;
            }
        },
    });
}

function cancel() {
    editing.value = false;
    form.name = props.club.name;
    form.city = props.club.city || '';
    form.logo = null;
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
        logoPreview.value = null;
    }
}

const displayLogo = () => logoPreview.value || props.club.logo_url;
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-5">
        <!-- View mode -->
        <div v-if="!editing" class="flex items-center gap-4">
            <div v-if="club.logo_url" class="h-14 w-14 shrink-0 overflow-hidden rounded-xl">
                <img :src="club.logo_url" :alt="club.name" class="h-full w-full object-cover" />
            </div>
            <div v-else class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ club.name }}</h3>
                <p v-if="club.city" class="text-sm text-gray-500">{{ club.city }}</p>
            </div>
            <button
                class="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                title="Modifier"
                @click="editing = true"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
            </button>
        </div>

        <!-- Edit mode -->
        <form v-else class="space-y-4" @submit.prevent="save">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div v-if="displayLogo()" class="h-16 w-16 overflow-hidden rounded-xl">
                        <img :src="displayLogo()" class="h-full w-full object-cover" alt="Logo" />
                    </div>
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-100">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75Z" /></svg>
                    </div>
                </div>
                <label class="cursor-pointer rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                    Changer le logo
                    <input type="file" accept="image/*" class="hidden" @input="handleLogo" />
                </label>
            </div>
            <InputError :message="form.errors.logo" />

            <div>
                <label class="block text-sm font-medium text-gray-700">Nom du club</label>
                <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Ville</label>
                <input v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="cancel">Annuler</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</template>

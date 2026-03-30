<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ form: Object });

const logoPreview = ref(null);

function handleLogo(e) {
    const file = e.target.files[0];
    if (!file) return;
    props.form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
}

function removeLogo() {
    props.form.logo = null;
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
        logoPreview.value = null;
    }
}
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Votre club</h2>
            <p class="mt-1 text-sm text-gray-500">Commencez par les informations de base.</p>
        </div>

        <!-- Logo -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Logo du club</label>
            <div class="mt-2 flex items-center gap-4">
                <div v-if="logoPreview" class="relative">
                    <img :src="logoPreview" class="h-16 w-16 rounded-xl object-cover ring-2 ring-emerald-100" alt="Logo" />
                    <button
                        type="button"
                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow transition hover:bg-red-600"
                        @click="removeLogo"
                    >
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-100">
                    <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75Z" /></svg>
                </div>
                <label class="cursor-pointer rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                    Choisir une image
                    <input type="file" accept="image/*" class="hidden" @input="handleLogo" />
                </label>
            </div>
            <p class="mt-1 text-xs text-gray-400">Optionnel. JPG, PNG. Max 5 Mo.</p>
        </div>

        <div>
            <label for="club_name" class="block text-sm font-medium text-gray-700">Nom du club</label>
            <input id="club_name" v-model="form.club_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex : Fosa Juniors FC" />
        </div>
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700">Ville</label>
            <input id="city" v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex : Mahajanga" />
        </div>
    </div>
</template>

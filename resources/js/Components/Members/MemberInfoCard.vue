<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ member: Object });

const form = useForm({
    first_name: props.member.first_name,
    last_name: props.member.last_name,
    phone: props.member.phone || '',
    birth_date: props.member.birth_date || '',
});

function submit() {
    form.put(route('members.update', props.member.id));
}
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Informations</h3>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prénom</label>
                    <input v-model="form.first_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    <InputError class="mt-1" :message="form.errors.first_name" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input v-model="form.last_name" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                    <input v-model="form.birth_date" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Enregistrer</button>
            </div>
        </form>
    </div>
</template>

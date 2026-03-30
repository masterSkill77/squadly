<script setup>
defineProps({ form: Object });
</script>

<template>
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Votre club</h2>
            <p class="mt-1 text-sm text-gray-500">Commencez par les informations de base.</p>
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

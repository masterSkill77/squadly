<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const emit = defineEmits(['close']);

function complete() {
    router.post(route('onboarding.complete'), {}, { preserveState: true });
    emit('close');
}

const steps = [
    {
        title: 'Bienvenue sur Squadly !',
        description: 'Vous venez de créer votre espace. Suivez ces étapes pour configurer votre club.',
        icon: '👋',
    },
    {
        title: 'Créez votre club',
        description: 'Ajoutez le nom, le logo et la ville de votre club pour commencer.',
        icon: '🏟️',
    },
    {
        title: 'Ajoutez vos sports',
        description: 'Football, basket, natation… Créez une section par sport pratiqué.',
        icon: '⚽',
    },
    {
        title: 'Formez vos équipes',
        description: 'Seniors, U17, féminines — organisez vos groupes comme vous le souhaitez.',
        icon: '👥',
    },
    {
        title: 'Invitez vos membres',
        description: 'Envoyez des invitations par email. Chacun recevra son accès avec le bon rôle.',
        icon: '📨',
    },
];

const current = ref(0);
const isLast = () => current.value === steps.length - 1;

function next() {
    if (isLast()) {
        complete();
    } else {
        current.value++;
    }
}

function prev() {
    if (current.value > 0) current.value--;
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
            <!-- Progress -->
            <div class="mb-6 flex gap-1.5">
                <div
                    v-for="(_, i) in steps"
                    :key="i"
                    class="h-1 flex-1 rounded-full transition-all"
                    :class="i <= current ? 'bg-emerald-500' : 'bg-gray-200'"
                />
            </div>

            <!-- Content -->
            <div class="text-center">
                <span class="text-4xl">{{ steps[current].icon }}</span>
                <h3 class="mt-4 text-xl font-bold text-gray-900">{{ steps[current].title }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ steps[current].description }}</p>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex items-center justify-between">
                <button
                    v-if="current > 0"
                    class="text-sm text-gray-400 transition hover:text-gray-600"
                    @click="prev"
                >
                    Précédent
                </button>
                <span v-else />

                <div class="flex items-center gap-3">
                    <button class="text-sm text-gray-400 transition hover:text-gray-600" @click="complete">
                        Passer
                    </button>
                    <button
                        class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="next"
                    >
                        {{ isLast() ? 'Commencer' : 'Suivant' }}
                    </button>
                </div>
            </div>

            <!-- Step counter -->
            <p class="mt-4 text-center text-xs text-gray-300">{{ current + 1 }} / {{ steps.length }}</p>
        </div>
    </div>
</template>

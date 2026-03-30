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
        description: 'Vous venez de créer votre espace. Suivez ces étapes pour découvrir tout ce que vous pouvez faire avec Squadly.',
        icon: '👋',
    },
    {
        title: 'Configurez votre club',
        description: 'Ajoutez le logo, le nom et la ville de votre club. Vous pouvez modifier ces informations à tout moment depuis la page Mon club.',
        icon: '🏟️',
    },
    {
        title: 'Ajoutez vos sports',
        description: 'Football, basket, natation, handball… Créez une section par sport pratiqué. Chaque section a ses propres champs de profil sportif.',
        icon: '⚽',
    },
    {
        title: 'Formez vos équipes',
        description: 'Seniors, U17, féminines — créez autant d\'équipes que nécessaire dans chaque section. Elles servent à organiser les événements et convocations.',
        icon: '👥',
    },
    {
        title: 'Invitez vos membres',
        description: 'Ajoutez des coachs et des joueurs par email. Chacun recevra un mail d\'invitation avec ses identifiants et un accès adapté à son rôle.',
        icon: '📨',
    },
    {
        title: 'Planifiez vos événements',
        description: 'Créez des entraînements et des matchs pour une équipe, une section entière ou tout le club. Envoyez les convocations en un clic.',
        icon: '📅',
    },
    {
        title: 'Suivez les présences',
        description: 'Faites l\'appel depuis l\'application et consultez les taux de présence par joueur. Un outil précieux pour le suivi de vos effectifs.',
        icon: '✅',
    },
    {
        title: 'Communiquez efficacement',
        description: 'Publiez des annonces ciblées avec des pièces jointes. Gérez les documents de vos membres (certificats, licences) et recevez des alertes d\'expiration.',
        icon: '📢',
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

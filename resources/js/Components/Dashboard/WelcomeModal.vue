<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Role, roleLabels } from '@/Utils/roles';

const props = defineProps({
    show: Boolean,
    role: String,
    clubName: String,
});
const emit = defineEmits(['close']);

const roleLabel = roleLabels[props.role] || props.role;

const steps = props.role === Role.Coach
    ? [
        {
            title: 'Bienvenue, Coach !',
            description: `Vous faites partie du club ${props.clubName}. Squadly vous aide à gérer vos équipes au quotidien.`,
            icon: '👋',
        },
        {
            title: 'Gérez vos effectifs',
            description: 'Accédez à la liste de vos joueurs, ajoutez de nouveaux membres et consultez leurs profils sportifs depuis la section Effectifs.',
            icon: '👥',
        },
        {
            title: 'Planifiez vos événements',
            description: 'Créez des entraînements, des matchs ou des événements spéciaux. Choisissez les équipes concernées et définissez le lieu et les horaires.',
            icon: '📅',
        },
        {
            title: 'Envoyez des convocations',
            description: 'Sélectionnez les joueurs à convoquer pour chaque événement. Ils recevront une notification et pourront confirmer leur présence.',
            icon: '📋',
        },
        {
            title: 'Suivez les présences',
            description: 'Faites l\'appel directement depuis l\'application. Consultez les taux de présence de chaque joueur pour mieux organiser vos séances.',
            icon: '✅',
        },
        {
            title: 'Communiquez avec vos équipes',
            description: 'Publiez des annonces ciblées pour une ou plusieurs équipes. Partagez des informations importantes, des fichiers ou des images.',
            icon: '📢',
        },
    ]
    : [
        {
            title: 'Bienvenue sur Squadly !',
            description: `Vous avez rejoint le club ${props.clubName}. Voici comment tirer le meilleur de votre espace.`,
            icon: '👋',
        },
        {
            title: 'Votre planning',
            description: 'Retrouvez tous vos événements à venir : entraînements, matchs et autres rendez-vous de votre équipe dans la section Événements.',
            icon: '📅',
        },
        {
            title: 'Répondez aux convocations',
            description: 'Quand votre coach vous convoque, vous recevez une notification. Confirmez ou déclinez votre présence en un clic.',
            icon: '📋',
        },
        {
            title: 'Suivez les annonces',
            description: 'Restez informé des actualités de votre club et de votre équipe grâce aux annonces publiées par vos coachs et l\'administration.',
            icon: '📢',
        },
        {
            title: 'Votre profil sportif',
            description: 'Votre fiche contient vos informations sportives : poste, numéro de maillot, pied fort… Demandez à votre coach de les mettre à jour.',
            icon: '👤',
        },
    ];

const current = ref(0);
const isLast = () => current.value === steps.length - 1;
const isFirst = () => current.value === 0;

function next() {
    if (isLast()) {
        close();
    } else {
        current.value++;
    }
}

function prev() {
    if (current.value > 0) current.value--;
}

function close() {
    router.post(route('onboarding.complete'), {}, { preserveState: true });
    current.value = 0;
    emit('close');
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
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

            <!-- Role badge (first step only) -->
            <div v-if="isFirst()" class="mt-4 flex justify-center">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ roleLabel }}
                </span>
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
                    <button class="text-sm text-gray-400 transition hover:text-gray-600" @click="close">
                        Passer
                    </button>
                    <button
                        class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="next"
                    >
                        {{ isLast() ? 'C\'est parti !' : 'Suivant' }}
                    </button>
                </div>
            </div>

            <!-- Step counter -->
            <p class="mt-4 text-center text-xs text-gray-300">{{ current + 1 }} / {{ steps.length }}</p>
        </div>
    </div>
</template>

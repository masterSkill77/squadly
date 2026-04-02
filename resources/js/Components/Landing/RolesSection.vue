<script setup>
const checkIcon = `<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" />`;

const roles = [
    {
        label: 'Président', color: 'purple', highlight: false,
        items: ['Vue globale sur toutes les sections', 'Gestion des membres et des rôles', 'Suivi des documents et alertes', 'Inscription aux compétitions'],
    },
    {
        label: 'Coach', color: 'emerald', highlight: true,
        items: ['Planning de son équipe', 'Envoi de convocations', 'Feuilles de présence et stats'],
    },
    {
        label: 'Membre / Joueur', color: 'blue', highlight: false,
        items: ['Son planning personnel', 'Réponse aux convocations', 'Profil et documents perso'],
    },
    {
        label: 'Organisateur', color: 'amber', highlight: false,
        items: ['Création de championnats et tournois', 'Gestion des inscriptions clubs', 'Calendrier et saisie des scores', 'Classements en temps réel'],
    },
];
</script>

<template>
    <section class="py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Une vue pour chaque rôle</h2>
                <p class="mt-4 text-lg text-gray-500">Chacun accède exactement à ce dont il a besoin.</p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="role in roles" :key="role.label" class="rounded-2xl border p-8" :class="role.highlight ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-100 bg-white'">
                    <div class="mb-4 inline-flex rounded-lg px-3 py-1 text-sm font-semibold" :class="`bg-${role.color}-50 text-${role.color}-700`">
                        {{ role.label }}
                    </div>
                    <ul class="mt-4 space-y-3 text-sm text-gray-600">
                        <li v-for="item in role.items" :key="item" class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20" v-html="checkIcon"></svg>
                            {{ item }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

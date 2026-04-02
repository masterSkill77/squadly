<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Landing/Navbar.vue';

const props = defineProps({
    clubs: Object,
    availableSports: Array,
    currentSport: String,
    canLogin: Boolean,
    canRegister: Boolean,
});

const sportEmojis = {
    football: '⚽', basketball: '🏀', handball: '🤾',
    natation: '🏊', rugby: '🏉', volleyball: '🏐',
};

const sportLabels = {
    football: 'Football', basketball: 'Basketball', handball: 'Handball',
    natation: 'Natation', rugby: 'Rugby', volleyball: 'Volleyball',
};

const gradients = [
    'from-emerald-400 to-teal-600',
    'from-orange-400 to-red-500',
    'from-blue-400 to-indigo-600',
    'from-pink-400 to-rose-600',
    'from-purple-400 to-violet-600',
    'from-amber-400 to-orange-600',
];

function goToPage(url) {
    if (url) window.location.href = url;
}
</script>

<template>
    <Head title="Clubs" />

    <div class="min-h-screen bg-gray-50">
        <Navbar :can-login="canLogin" :can-register="canRegister" />

        <div class="bg-white pb-8 pt-32">
            <div class="mx-auto max-w-6xl px-6">
                <h1 class="text-3xl font-bold text-gray-900">Clubs</h1>
                <p class="mt-2 text-gray-500">Découvrez les clubs sportifs sur Squadly.</p>

                <!-- Sport filter -->
                <div v-if="availableSports?.length" class="mt-6 flex flex-wrap gap-2">
                    <button
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="!currentSport ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        @click="router.get(route('clubs.index'), {}, { preserveState: true })"
                    >
                        Tous
                    </button>
                    <button
                        v-for="sport in availableSports"
                        :key="sport"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="currentSport === sport ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        @click="router.get(route('clubs.index'), { sport }, { preserveState: true })"
                    >
                        {{ sportEmojis[sport] || '🏅' }} {{ sportLabels[sport] || sport }}
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 py-8">
            <div v-if="clubs.data?.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="(club, i) in clubs.data"
                    :key="club.id"
                    :href="route('clubs.show', club.slug)"
                    class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:shadow-lg"
                >
                    <div
                        class="relative flex h-28 items-center justify-center bg-gradient-to-br"
                        :class="gradients[i % gradients.length]"
                    >
                        <div v-if="club.logo_url" class="absolute inset-0">
                            <img :src="club.logo_url" :alt="club.name" class="h-full w-full object-cover opacity-30" />
                        </div>
                        <span class="relative text-4xl font-extrabold text-white/90">
                            {{ club.name.substring(0, 2).toUpperCase() }}
                        </span>
                        <div class="absolute bottom-2 right-2 flex gap-1">
                            <span
                                v-for="sport in club.sports?.slice(0, 4)"
                                :key="sport"
                                class="rounded-full bg-white/20 px-1.5 py-0.5 text-xs backdrop-blur-sm"
                            >
                                {{ sportEmojis[sport] || '🏅' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700">{{ club.name }}</h3>
                        <p v-if="club.city" class="mt-0.5 text-sm text-gray-500">{{ club.city }}</p>
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-400">
                            <span>{{ club.members_count || 0 }} membres</span>
                            <span>{{ club.teams_count || 0 }} équipes</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="rounded-2xl border border-dashed border-gray-300 p-16 text-center">
                <p class="text-gray-500">Aucun club pour le moment.</p>
            </div>

            <!-- Pagination -->
            <div v-if="clubs.last_page > 1" class="mt-8 flex items-center justify-between">
                <p class="text-xs text-gray-500">Page {{ clubs.current_page }} / {{ clubs.last_page }}</p>
                <div class="flex gap-2">
                    <button
                        v-for="link in clubs.links"
                        :key="link.label"
                        :disabled="!link.url"
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                        :class="link.active ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : link.url ? 'border-gray-200 text-gray-600 hover:bg-gray-50' : 'border-gray-100 text-gray-300 cursor-not-allowed'"
                        @click="goToPage(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

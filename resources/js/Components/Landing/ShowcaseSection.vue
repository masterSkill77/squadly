<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    clubs: { type: Array, default: () => [] },
});

const sportEmojis = {
    football: '⚽', basketball: '🏀', handball: '🤾',
    natation: '🏊', rugby: '🏉', volleyball: '🏐',
};

const gradients = [
    'from-emerald-400 to-teal-600',
    'from-orange-400 to-red-500',
    'from-blue-400 to-indigo-600',
    'from-pink-400 to-rose-600',
    'from-purple-400 to-violet-600',
    'from-amber-400 to-orange-600',
    'from-cyan-400 to-blue-600',
    'from-lime-400 to-green-600',
];

// Fallback data if no clubs from DB
const fallbackClubs = [
    { name: 'Fosa Juniors FC', city: 'Mahajanga', sports: ['football'], members_count: 22, teams: [{ name: 'Seniors', sport: 'football', members_count: 22 }] },
    { name: 'Basket Club Tana', city: 'Antananarivo', sports: ['basketball'], members_count: 14, teams: [{ name: 'U17', sport: 'basketball', members_count: 14 }] },
    { name: 'CN Mahajanga', city: 'Mahajanga', sports: ['natation'], members_count: 18, teams: [{ name: 'Compétition', sport: 'natation', members_count: 18 }] },
    { name: 'HB Toamasina', city: 'Toamasina', sports: ['handball'], members_count: 16, teams: [{ name: 'Féminines', sport: 'handball', members_count: 16 }] },
];

const displayClubs = props.clubs?.length ? props.clubs : fallbackClubs;
</script>

<template>
    <section class="py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Ils utilisent déjà Squadly</h2>
                <p class="mt-4 text-lg text-gray-500">Des clubs de tous les sports nous font confiance.</p>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="(club, i) in displayClubs.slice(0, 8)"
                    :key="club.name"
                    :href="club.slug ? `/clubs/${club.slug}` : '#'"
                    class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:shadow-lg"
                >
                    <!-- Header gradient with initials -->
                    <div
                        class="relative flex h-32 items-center justify-center bg-gradient-to-br"
                        :class="gradients[i % gradients.length]"
                    >
                        <div v-if="club.logo_url" class="absolute inset-0">
                            <img :src="club.logo_url" :alt="club.name" class="h-full w-full object-cover opacity-30" />
                        </div>
                        <span class="relative text-4xl font-extrabold text-white/90">
                            {{ club.name.substring(0, 2).toUpperCase() }}
                        </span>
                        <!-- Sport badges -->
                        <div class="absolute bottom-2 right-2 flex gap-1">
                            <span
                                v-for="sport in club.sports?.slice(0, 3)"
                                :key="sport"
                                class="rounded-full bg-white/20 px-1.5 py-0.5 text-xs backdrop-blur-sm"
                            >
                                {{ sportEmojis[sport] || '🏅' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700">{{ club.name }}</h3>
                        <p v-if="club.city" class="mt-0.5 text-xs text-gray-500">{{ club.city }}</p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                            <span>{{ club.members_count || 0 }} membres</span>
                            <span>{{ club.teams?.length || 0 }} équipes</span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>

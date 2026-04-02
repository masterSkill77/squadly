<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const user = usePage().props.auth.user;
</script>

<template>
    <nav class="fixed top-0 z-50 w-full border-b border-gray-100 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
                <img src="/squadly-logo-light.svg" alt="Squadly" class="h-10" />
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('clubs.index')" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-emerald-700">
                    Clubs
                </Link>
                <Link :href="route('competitions.index')" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-emerald-700">
                    Compétitions
                </Link>
                <a href="/squadly-pitch-deck.html" target="_blank" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-gray-900">
                    Pitch Deck
                </a>
                <template v-if="user">
                    <Link :href="route('dashboard')" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                        Tableau de bord
                    </Link>
                </template>
                <template v-else>
                    <Link v-if="canLogin" :href="route('login')" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-gray-900">
                        Connexion
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                        S'inscrire
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>

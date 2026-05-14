<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const user = usePage().props.auth.user;
const mobileMenuOpen = ref(false);
</script>

<template>
    <nav class="fixed top-0 z-50 w-full border-b border-gray-100 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
                <img src="/squadly-logo-light.svg" alt="Squadly" class="h-10" />
            </div>

            <!-- Desktop links -->
            <div class="hidden items-center gap-3 md:flex">
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

            <!-- Mobile hamburger button -->
            <button
                class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 md:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen"
                aria-label="Menu"
            >
                <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="border-t border-gray-100 bg-white md:hidden">
            <div class="flex flex-col space-y-1 px-4 py-3">
                <Link
                    :href="route('clubs.index')"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-emerald-700"
                    @click="mobileMenuOpen = false"
                >
                    Clubs
                </Link>
                <Link
                    :href="route('competitions.index')"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-emerald-700"
                    @click="mobileMenuOpen = false"
                >
                    Compétitions
                </Link>
                <a
                    href="/squadly-pitch-deck.html"
                    target="_blank"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-900"
                    @click="mobileMenuOpen = false"
                >
                    Pitch Deck
                </a>
                <div class="pt-1">
                    <template v-if="user">
                        <Link
                            :href="route('dashboard')"
                            class="block rounded-lg bg-emerald-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-emerald-700"
                            @click="mobileMenuOpen = false"
                        >
                            Tableau de bord
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="block rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-gray-900"
                            @click="mobileMenuOpen = false"
                        >
                            Connexion
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="mt-1 block rounded-lg bg-emerald-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-emerald-700"
                            @click="mobileMenuOpen = false"
                        >
                            S'inscrire
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

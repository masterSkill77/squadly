<script setup>
import { Link } from '@inertiajs/vue3';
import HeroImages from './HeroImages.vue';

defineProps({
    canRegister: Boolean,
    sports: Array,
    stats: Object,
});
</script>

<template>
    <section class="relative overflow-hidden pt-32 pb-28">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-teal-50"></div>
        <div class="relative mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <!-- Text -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-sm font-medium text-emerald-700">
                        <span v-for="sport in sports" :key="sport.name" v-html="sport.emoji"></span>
                        <span class="ml-1">Multi-sports</span>
                    </div>
                    <h1 class="mt-6 text-5xl leading-tight font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                        Gérez moins. <span class="text-emerald-600">Jouez plus.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-gray-600">
                        Squadly simplifie la gestion de votre club sportif et de vos compétitions : membres, équipes, planning, championnats et classements — tout en un seul endroit.
                    </p>
                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                        <Link v-if="canRegister" :href="route('register')" class="rounded-xl bg-emerald-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700 hover:shadow-emerald-300">
                            Commencer gratuitement
                        </Link>
                        <a href="#features" class="rounded-xl border border-gray-200 bg-white px-8 py-3.5 text-base font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                            Découvrir
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-gray-400">Gratuit pour les clubs de moins de 50 membres</p>

                    <!-- Live stats -->
                    <div v-if="stats && (stats.clubs > 0)" class="mt-8 flex items-center gap-6 lg:justify-start justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ stats.clubs }}</p>
                            <p class="text-xs text-gray-500">Clubs</p>
                        </div>
                        <div class="h-8 w-px bg-gray-200" />
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ stats.members }}</p>
                            <p class="text-xs text-gray-500">Membres</p>
                        </div>
                        <div class="h-8 w-px bg-gray-200" />
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ stats.teams }}</p>
                            <p class="text-xs text-gray-500">Équipes</p>
                        </div>
                        <div v-if="stats.competitions > 0" class="h-8 w-px bg-gray-200" />
                        <div v-if="stats.competitions > 0" class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ stats.competitions }}</p>
                            <p class="text-xs text-gray-500">Compétitions</p>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <HeroImages />
            </div>
        </div>
    </section>
</template>

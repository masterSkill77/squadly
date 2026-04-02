<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ canRegister: Boolean });
</script>

<template>
    <section class="bg-emerald-600 py-20">
        <div class="mx-auto max-w-6xl px-6 text-center">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Prêt à simplifier la gestion de votre club ou de vos compétitions ?</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-emerald-100">Club ou organisateur, rejoignez Squadly et concentrez-vous sur l'essentiel : le sport.</p>
            <Link v-if="canRegister" :href="route('register')" class="mt-8 inline-block rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-emerald-700 shadow-lg transition hover:bg-emerald-50">
                Commencer gratuitement
            </Link>
        </div>
    </section>
</template>

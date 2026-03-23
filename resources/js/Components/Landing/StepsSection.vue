<script setup>
defineProps({ steps: Array });
</script>

<template>
    <section class="bg-gray-50 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Prêt en 4 étapes</h2>
                <p class="mt-4 text-lg text-gray-500">De l'inscription à la première convocation en quelques minutes.</p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="step in steps" :key="step.number" class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-xl font-bold text-white">{{ step.number }}</div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ step.title }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ step.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import FeatureIcon from './FeatureIcon.vue';

defineProps({ features: Array });
</script>

<template>
    <section id="features" class="py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Tout ce qu'il faut pour gérer votre club</h2>
                <p class="mt-4 text-lg text-gray-500">Des outils pensés par et pour les clubs sportifs.</p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="f in features" :key="f.title" class="group rounded-2xl border border-gray-100 bg-white p-8 transition hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                        <FeatureIcon :name="f.icon" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ f.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ f.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

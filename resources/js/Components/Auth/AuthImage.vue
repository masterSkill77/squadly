<script setup>
defineProps({
    src: String,
    alt: { type: String, default: '' },
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="hidden lg:block lg:w-1/2">
        <div class="relative h-screen">
            <img :src="src" :alt="alt" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/70 to-emerald-800/30"></div>
            <div class="absolute bottom-12 left-12 right-12 text-white">
                <h2 class="text-3xl font-bold">{{ title }}</h2>
                <p class="mt-3 text-emerald-100">{{ subtitle }}</p>
            </div>
        </div>
    </div>
</template>

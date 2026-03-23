<script setup>
defineProps({ sports: Array });
</script>

<template>
    <section class="border-y border-gray-100 bg-gray-50 py-8">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-8 px-6">
            <div v-for="sport in sports" :key="sport.name" class="flex items-center gap-2 text-gray-500">
                <span class="text-2xl" v-html="sport.emoji"></span>
                <span class="text-sm font-medium">{{ sport.name }}</span>
            </div>
        </div>
    </section>
</template>

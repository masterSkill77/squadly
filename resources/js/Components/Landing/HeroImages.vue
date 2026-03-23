<script setup>
const front = 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=700&h=460&fit=crop';
const back = 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=700&h=460&fit=crop';
</script>

<template>
    <div class="relative mx-auto mt-14 h-72 w-full max-w-lg sm:h-80 lg:mt-0">
        <div class="absolute inset-x-4 bottom-0 h-full overflow-hidden rounded-2xl shadow-lg">
            <img :src="back" alt="Match de basketball" class="h-full w-full object-cover" />
        </div>
        <div class="absolute inset-0 -rotate-3 overflow-hidden rounded-2xl shadow-2xl ring-4 ring-white">
            <img :src="front" alt="Équipe de football" class="h-full w-full object-cover" />
        </div>
    </div>
</template>

<script setup>
const squads = [
    {
        name: 'Fosa Juniors FC — Seniors',
        sport: 'Football',
        members: 22,
        gradient: 'from-emerald-400 to-teal-600',
        image: 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=600&h=400&fit=crop',
    },
    {
        name: 'Fikambanan\'ny Basket Tana — U17',
        sport: 'Basketball',
        members: 14,
        gradient: 'from-orange-400 to-red-500',
        image: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=600&h=400&fit=crop',
    },
    {
        name: 'CN Mahajanga — Compétition',
        sport: 'Natation',
        members: 18,
        gradient: 'from-blue-400 to-indigo-600',
        image: 'https://images.unsplash.com/photo-1519315901367-f34ff9154487?w=600&h=400&fit=crop',
    },
    {
        name: 'Akon\'i Toamasina HB — Féminines',
        sport: 'Handball',
        members: 16,
        gradient: 'from-pink-400 to-rose-600',
        image: 'https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?w=600&h=400&fit=crop',
    },
];

function onImgError(e) {
    e.target.style.display = 'none';
    e.target.nextElementSibling.style.display = 'flex';
}
</script>

<template>
    <section class="py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Des équipes de tous les sports</h2>
                <p class="mt-4 text-lg text-gray-500">Squadly s'adapte à chaque discipline et chaque catégorie.</p>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="squad in squads" :key="squad.name" class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:shadow-lg">
                    <div class="relative h-40">
                        <img :src="squad.image" :alt="squad.name" class="h-full w-full object-cover" loading="lazy" @error="onImgError" />
                        <div :class="`bg-gradient-to-br ${squad.gradient}`" class="hidden h-full items-center justify-center"></div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-sm font-semibold text-gray-900">{{ squad.name }}</h3>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ squad.sport }}</span>
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">{{ squad.members }} membres</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

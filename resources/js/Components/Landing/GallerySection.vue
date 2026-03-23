<script setup>
const photos = [
    {
        src: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&h=500&fit=crop',
        alt: 'Équipe de football sur le terrain',
        span: 'sm:col-span-2 sm:row-span-2',
    },
    {
        src: 'https://images.unsplash.com/photo-1515523110800-9415d13b84a8?w=500&h=350&fit=crop',
        alt: 'Match de basketball en salle',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1560089000-7433a4ebbd64?w=500&h=350&fit=crop',
        alt: 'Nageurs en compétition',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=500&h=350&fit=crop',
        alt: 'Équipe célébrant une victoire',
        span: '',
    },
    {
        src: 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=500&h=350&fit=crop',
        alt: 'Rugby — mêlée en plein match',
        span: '',
    },
];
</script>

<template>
    <section class="bg-gray-50 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Le sport, c'est avant tout le terrain</h2>
                <p class="mt-4 text-lg text-gray-500">Squadly gère l'administratif pour que vous restiez concentrés sur le jeu.</p>
            </div>
            <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-4 sm:grid-rows-2">
                <div
                    v-for="photo in photos"
                    :key="photo.alt"
                    :class="photo.span"
                    class="overflow-hidden rounded-2xl"
                >
                    <img
                        :src="photo.src"
                        :alt="photo.alt"
                        class="h-full w-full object-cover transition duration-500 hover:scale-105"
                        loading="lazy"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

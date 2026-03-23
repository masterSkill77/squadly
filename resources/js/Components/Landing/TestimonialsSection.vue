<script setup>
const testimonials = [
    {
        name: 'Hery Rasolofoniaina',
        role: 'Président — Fosa Juniors FC, Mahajanga',
        text: 'Avant Squadly, on gérait tout par SMS et tableurs Excel. Maintenant, les convocations partent en un clic et les joueurs répondent directement.',
        initials: 'HR',
        color: 'bg-emerald-500',
    },
    {
        name: 'Naina Andriamanalina',
        role: 'Coach U17 — Fikambanan\'ny Basket Tana',
        text: "Les feuilles de présence digitales m'ont changé la vie. Je vois en un coup d'œil qui était là, qui manque souvent, et je peux adapter mes séances.",
        initials: 'NA',
        color: 'bg-blue-500',
    },
    {
        name: 'Volatiana Razafindrakoto',
        role: 'Joueuse — CN Mahajanga Natation',
        text: "J'ai mon planning sur le téléphone, je confirme ma présence en deux secondes. Plus besoin de chercher dans les groupes WhatsApp.",
        initials: 'VR',
        color: 'bg-purple-500',
    },
];
</script>

<template>
    <section class="bg-gray-50 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Ils utilisent Squadly</h2>
                <p class="mt-4 text-lg text-gray-500">Admins, coachs et joueurs — tous y trouvent leur compte.</p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-3">
                <div v-for="t in testimonials" :key="t.name" class="rounded-2xl border border-gray-100 bg-white p-8">
                    <p class="text-sm leading-relaxed text-gray-600 italic">"{{ t.text }}"</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div :class="t.color" class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white">
                            {{ t.initials }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ t.name }}</p>
                            <p class="text-xs text-gray-500">{{ t.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const player = {
    name: 'Tahiry Rakoto',
    age: 16,
    team: 'U17 — Fosa Juniors FC',
    position: 'Attaquant',
    jersey: '#10',
    foot: 'Droit',
    stats: [
        { label: 'Buts', value: '12' },
        { label: 'Présence', value: '94%' },
        { label: 'Matchs', value: '18' },
        { label: 'Convocations', value: '20' },
    ],
};
</script>

<template>
    <section class="py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <!-- Text -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Le profil complet de chaque joueur</h2>
                    <p class="mt-4 text-lg text-gray-500">Stats, présences, convocations — tout est centralisé. Le coach et le joueur voient la même chose.</p>
                    <ul class="mt-8 space-y-3 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" /></svg>
                            Profil adapté au sport (poste, pied fort, maillot…)
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" /></svg>
                            Taux de présence calculé automatiquement
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" /></svg>
                            Historique des convocations et réponses
                        </li>
                    </ul>
                </div>

                <!-- Card -->
                <div class="mx-auto w-full max-w-xs rounded-2xl border border-gray-100 bg-white p-6 shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">TR</div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ player.name }}</p>
                            <p class="text-xs text-gray-500">{{ player.team }}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Poste</p>
                            <p class="font-semibold text-gray-900">{{ player.position }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Maillot</p>
                            <p class="font-semibold text-emerald-600">{{ player.jersey }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Pied fort</p>
                            <p class="font-semibold text-gray-900">{{ player.foot }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Âge</p>
                            <p class="font-semibold text-gray-900">{{ player.age }} ans</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-2 rounded-xl bg-emerald-50 p-3 text-center">
                        <div v-for="s in player.stats" :key="s.label">
                            <p class="text-sm font-bold text-emerald-700">{{ s.value }}</p>
                            <p class="text-[9px] text-emerald-600/70">{{ s.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

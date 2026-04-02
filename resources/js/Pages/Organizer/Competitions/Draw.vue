<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    competition: Object,
});

const phases = computed(() => props.competition.phases ?? []);
const approvedClubs = computed(() => props.competition.competition_clubs ?? []);

// Draw state
const isDrawing = ref(false);
const drawComplete = ref(false);
const drawnPhases = ref([]);
const revealedCount = ref(0);
const totalToDraw = ref(0);

// Flat list of all draws in order for sequential animation
const drawSequence = ref([]);

// Check if clubs are already assigned to phases
const alreadyDrawn = computed(() =>
    phases.value.some(p => (p.competition_clubs ?? []).length > 0)
);

const sportEmojis = {
    football: '⚽', basketball: '🏀', handball: '🤾',
    natation: '🏊', rugby: '🏉', volleyball: '🏐',
};

function startDraw() {
    if (approvedClubs.value.length < 2 || phases.value.length === 0) return;

    isDrawing.value = true;
    drawComplete.value = false;
    revealedCount.value = 0;

    // Shuffle clubs
    const clubs = [...approvedClubs.value]
        .map(cc => ({ id: cc.club_id, name: cc.club?.name, city: cc.club?.city }))
        .sort(() => Math.random() - 0.5);

    totalToDraw.value = clubs.length;

    // Distribute into phases
    const perPhase = Math.ceil(clubs.length / phases.value.length);
    drawnPhases.value = phases.value.map((phase, i) => ({
        phase_id: phase.id,
        phase_name: phase.name,
        clubs: clubs.slice(i * perPhase, (i + 1) * perPhase).map(c => ({
            ...c,
            revealed: false,
        })),
    }));

    // Build flat sequence: [{ phaseIndex, clubIndex }]
    // Distributes round-robin across phases for a more dramatic effect
    // Phase A gets 1st, Phase B gets 2nd, Phase A gets 3rd...
    drawSequence.value = [];
    const maxPerPhase = Math.max(...drawnPhases.value.map(p => p.clubs.length));
    for (let slot = 0; slot < maxPerPhase; slot++) {
        for (let pi = 0; pi < drawnPhases.value.length; pi++) {
            if (slot < drawnPhases.value[pi].clubs.length) {
                drawSequence.value.push({ pi, ci: slot });
            }
        }
    }

    // Start sequential reveals
    revealNext(0);
}

function revealNext(index) {
    if (index >= drawSequence.value.length) {
        setTimeout(() => { drawComplete.value = true; }, 600);
        return;
    }

    const { pi, ci } = drawSequence.value[index];

    setTimeout(() => {
        drawnPhases.value[pi].clubs[ci].revealed = true;
        revealedCount.value++;
        revealNext(index + 1);
    }, 1000 + Math.random() * 400); // slight randomness for suspense
}

function confirmDraw() {
    router.post(route('organizer.competitions.perform-draw', props.competition.id), {}, {
        onSuccess: () => {
            router.visit(route('organizer.competitions.show', props.competition.id));
        },
    });
}

function generateMatches() {
    router.post(route('organizer.competitions.auto-generate', props.competition.id));
}

function confirmAndGenerate() {
    router.post(route('organizer.competitions.perform-draw', props.competition.id), {}, {
        onSuccess: () => {
            router.post(route('organizer.competitions.auto-generate', props.competition.id));
        },
    });
}
</script>

<template>
    <Head :title="`Tirage au sort — ${competition.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('organizer.competitions.show', competition.id)"
                    class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Tirage au sort</h2>
                    <p class="text-xs text-gray-500">{{ competition.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Info bar -->
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="text-3xl">{{ sportEmojis[competition.sport_type] || '🏅' }}</span>
                    <div>
                        <p class="font-semibold text-gray-900">{{ approvedClubs.length }} clubs inscrits</p>
                        <p class="text-sm text-gray-500">{{ phases.length }} groupe(s) configuré(s)</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        v-if="!isDrawing && !alreadyDrawn"
                        :disabled="approvedClubs.length < 2 || phases.length === 0"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50"
                        @click="startDraw"
                    >
                        <svg class="h-5 w-5 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" /></svg>
                        Lancer le tirage
                    </button>
                    <button
                        v-if="alreadyDrawn && !isDrawing"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700"
                        @click="generateMatches"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        Générer les matchs
                    </button>
                </div>
            </div>

            <!-- Pre-draw: clubs as animated balls -->
            <div v-if="!isDrawing && !alreadyDrawn" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Clubs participants — en attente du tirage</h3>
                <div class="flex flex-wrap gap-4">
                    <div
                        v-for="(cc, i) in approvedClubs"
                        :key="cc.id"
                        class="group relative flex h-16 w-16 items-center justify-center rounded-full border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-100 text-sm font-bold text-emerald-700 shadow-md transition-transform hover:scale-110"
                        :title="cc.club?.name"
                        :style="{ animationDelay: `${i * 0.1}s` }"
                    >
                        <span class="animate-float">{{ (cc.club?.name || '?').substring(0, 2).toUpperCase() }}</span>
                        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] text-gray-500 opacity-0 transition group-hover:opacity-100">
                            {{ cc.club?.name }}
                        </span>
                    </div>
                </div>
                <p v-if="approvedClubs.length < 2" class="mt-6 text-sm text-amber-600">Il faut au moins 2 clubs approuvés pour lancer le tirage.</p>
                <p v-if="phases.length === 0" class="mt-6 text-sm text-amber-600">Créez d'abord des groupes/phases pour la compétition.</p>
            </div>

            <!-- Drawing animation -->
            <div v-if="isDrawing" class="space-y-6">
                <!-- Progress -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">
                            <template v-if="!drawComplete">Tirage en cours…</template>
                            <template v-else>Tirage terminé !</template>
                        </span>
                        <span class="tabular-nums text-gray-500">{{ revealedCount }} / {{ totalToDraw }}</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full transition-all duration-700 ease-out"
                            :class="drawComplete ? 'bg-emerald-500' : 'bg-emerald-400 animate-pulse'"
                            :style="{ width: `${(revealedCount / totalToDraw) * 100}%` }"
                        />
                    </div>
                </div>

                <!-- Phases/Groups grid -->
                <div class="grid gap-6" :class="drawnPhases.length === 2 ? 'lg:grid-cols-2' : drawnPhases.length >= 3 ? 'lg:grid-cols-3' : ''">
                    <div
                        v-for="(phase, pi) in drawnPhases"
                        :key="phase.phase_id"
                        class="rounded-xl border-2 bg-white p-5 shadow-sm transition-all duration-500"
                        :class="[
                            phase.clubs.every(c => c.revealed) ? 'border-emerald-400 shadow-emerald-100' : 'border-gray-200',
                            phase.clubs.some(c => c.revealed) && !phase.clubs.every(c => c.revealed) ? 'border-amber-300 shadow-amber-50' : ''
                        ]"
                    >
                        <!-- Phase header -->
                        <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold transition-colors duration-500"
                                :class="phase.clubs.every(c => c.revealed)
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-gray-100 text-gray-600'"
                            >
                                {{ String.fromCharCode(65 + pi) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ phase.phase_name }}</h3>
                                <p class="text-xs text-gray-500">
                                    {{ phase.clubs.filter(c => c.revealed).length }} / {{ phase.clubs.length }} club(s)
                                </p>
                            </div>
                        </div>

                        <!-- Club slots -->
                        <div class="space-y-2.5">
                            <template v-for="(club, ci) in phase.clubs" :key="ci">
                                <!-- Revealed club -->
                                <div
                                    v-if="club.revealed"
                                    class="draw-card-enter flex items-center gap-3 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-white p-3"
                                >
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white shadow-lg draw-ball-pop">
                                        {{ (club.name || '?').substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ club.name }}</p>
                                        <p v-if="club.city" class="text-xs text-gray-500">{{ club.city }}</p>
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-emerald-500 draw-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </div>

                                <!-- Empty slot -->
                                <div
                                    v-else
                                    class="flex items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-3"
                                >
                                    <div class="h-11 w-11 shrink-0 animate-pulse rounded-full bg-gray-200" />
                                    <div class="flex-1 space-y-1.5">
                                        <div class="h-3.5 w-28 animate-pulse rounded-md bg-gray-200" />
                                        <div class="h-2.5 w-16 animate-pulse rounded-md bg-gray-100" />
                                    </div>
                                    <div class="text-xs font-medium text-gray-400">?</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Confirm actions -->
                <Transition
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-4"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="drawComplete" class="rounded-xl border-2 border-emerald-300 bg-gradient-to-r from-emerald-50 to-white p-6 text-center shadow-lg shadow-emerald-100">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-lg shadow-emerald-200 draw-ball-pop">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-emerald-900">Tirage terminé !</h3>
                        <p class="mt-1 text-sm text-emerald-700">Les clubs ont été répartis dans les groupes. Confirmez pour sauvegarder.</p>
                        <div class="mt-5 flex flex-wrap justify-center gap-3">
                            <button
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                @click="isDrawing = false; drawComplete = false"
                            >
                                Annuler
                            </button>
                            <button
                                class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                                @click="confirmDraw"
                            >
                                Confirmer le tirage
                            </button>
                            <button
                                class="rounded-lg bg-purple-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700"
                                @click="confirmAndGenerate"
                            >
                                Confirmer + Générer les matchs
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Already drawn: show current assignment -->
            <div v-if="alreadyDrawn && !isDrawing" class="space-y-6">
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                    Le tirage a déjà été effectué. Vous pouvez générer les matchs ou relancer un nouveau tirage.
                </div>

                <div class="grid gap-6" :class="phases.length === 2 ? 'lg:grid-cols-2' : phases.length >= 3 ? 'lg:grid-cols-3' : ''">
                    <div
                        v-for="(phase, pi) in phases"
                        :key="phase.id"
                        class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white">
                                {{ String.fromCharCode(65 + pi) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ phase.name }}</h3>
                                <p class="text-xs text-gray-500">{{ (phase.competition_clubs ?? []).length }} club(s)</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="cc in (phase.competition_clubs ?? [])"
                                :key="cc.id"
                                class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3"
                            >
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">
                                    {{ (cc.club?.name || '?').substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ cc.club?.name }}</p>
                                    <p v-if="cc.club?.city" class="text-xs text-gray-500">{{ cc.club?.city }}</p>
                                </div>
                            </div>
                            <p v-if="!(phase.competition_clubs ?? []).length" class="py-4 text-center text-sm text-gray-400">Aucun club assigné</p>
                        </div>
                    </div>
                </div>

                <!-- No "Relancer" button — draw is already confirmed -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes ball-pop {
    0% { transform: scale(0) rotate(-180deg); opacity: 0; }
    60% { transform: scale(1.3) rotate(10deg); opacity: 1; }
    80% { transform: scale(0.9) rotate(-5deg); }
    100% { transform: scale(1) rotate(0deg); }
}

@keyframes card-enter {
    0% { transform: translateX(-30px) scale(0.8); opacity: 0; }
    60% { transform: translateX(5px) scale(1.02); opacity: 1; }
    100% { transform: translateX(0) scale(1); }
}

@keyframes check-draw {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.4); opacity: 1; }
    100% { transform: scale(1); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.draw-ball-pop {
    animation: ball-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.draw-card-enter {
    animation: card-enter 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.draw-check {
    animation: check-draw 0.4s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>

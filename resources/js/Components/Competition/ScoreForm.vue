<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    game: { type: Object, required: true },
});

const emit = defineEmits(['submit']);

const form = useForm({
    home_score: props.game.home_score ?? 0,
    away_score: props.game.away_score ?? 0,
});

function submit() {
    emit('submit', {
        home_score: form.home_score,
        away_score: form.away_score,
    });
}
</script>

<template>
    <form class="rounded-xl border border-gray-100 bg-white p-5" @submit.prevent="submit">
        <h4 class="text-sm font-semibold text-gray-900">Saisir le score</h4>

        <div class="mt-4 flex items-end justify-center gap-4">
            <!-- Home -->
            <div class="flex-1 text-center">
                <label class="mb-1 block text-xs font-medium text-gray-600">{{ game.homeClub }}</label>
                <input
                    v-model.number="form.home_score"
                    type="number"
                    min="0"
                    class="w-full rounded-lg border-gray-300 text-center text-lg font-bold text-gray-900 focus:border-emerald-500 focus:ring-emerald-500"
                />
            </div>

            <span class="pb-2 text-sm font-medium text-gray-400">-</span>

            <!-- Away -->
            <div class="flex-1 text-center">
                <label class="mb-1 block text-xs font-medium text-gray-600">{{ game.awayClub }}</label>
                <input
                    v-model.number="form.away_score"
                    type="number"
                    min="0"
                    class="w-full rounded-lg border-gray-300 text-center text-lg font-bold text-gray-900 focus:border-emerald-500 focus:ring-emerald-500"
                />
            </div>
        </div>

        <div class="mt-5 flex justify-end">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
            >
                Enregistrer le score
            </button>
        </div>
    </form>
</template>

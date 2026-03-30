<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import StepClub from '@/Components/Onboarding/StepClub.vue';
import StepSports from '@/Components/Onboarding/StepSports.vue';
import StepTeams from '@/Components/Onboarding/StepTeams.vue';
import StepReview from '@/Components/Onboarding/StepReview.vue';

const props = defineProps({ sportTemplates: Array });

const form = useForm({
    club_name: '',
    city: '',
    sports: [],
    teams: [{ name: '', sport: '', age_category: '' }],
});

const step = ref(0);
const steps = ['Club', 'Sports', 'Équipes', 'Récapitulatif'];

const canNext = computed(() => {
    if (step.value === 0) return form.club_name.trim().length > 0;
    if (step.value === 1) return form.sports.length > 0;
    if (step.value === 2) return form.teams.length > 0 && form.teams.every(t => t.name.trim() && t.sport);
    return true;
});

function next() {
    if (step.value === 1 && form.teams[0] && !form.teams[0].sport) {
        form.teams[0].sport = form.sports[0];
    }
    if (step.value < steps.length - 1) step.value++;
}

function prev() {
    if (step.value > 0) step.value--;
}

function submit() {
    form.post(route('onboarding.store'));
}
</script>

<template>
    <Head title="Configurer votre club" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
        <div class="w-full max-w-lg">
            <!-- Logo -->
            <div class="mb-8 text-center">
                <img src="/squadly-logo-light.svg" alt="Squadly" class="mx-auto h-10" />
            </div>

            <!-- Progress -->
            <div class="mb-8 flex items-center gap-2">
                <template v-for="(s, i) in steps" :key="i">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                            :class="i <= step ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'"
                        >
                            {{ i + 1 }}
                        </div>
                        <span class="hidden text-sm font-medium sm:inline" :class="i <= step ? 'text-gray-900' : 'text-gray-400'">{{ s }}</span>
                    </div>
                    <div v-if="i < steps.length - 1" class="h-px flex-1" :class="i < step ? 'bg-emerald-500' : 'bg-gray-200'" />
                </template>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
                <StepClub v-if="step === 0" :form="form" />
                <StepSports v-else-if="step === 1" :form="form" :sport-templates="sportTemplates" />
                <StepTeams v-else-if="step === 2" :form="form" />
                <StepReview v-else :form="form" />

                <!-- Errors -->
                <div v-if="Object.keys(form.errors).length" class="mt-4 rounded-lg bg-red-50 p-3">
                    <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-red-600">{{ msg }}</p>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-between">
                    <button
                        v-if="step > 0"
                        type="button"
                        class="text-sm text-gray-500 transition hover:text-gray-700"
                        @click="prev"
                    >
                        Précédent
                    </button>
                    <span v-else />

                    <button
                        v-if="step < steps.length - 1"
                        type="button"
                        :disabled="!canNext"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40"
                        @click="next"
                    >
                        Suivant
                    </button>
                    <button
                        v-else
                        type="button"
                        :disabled="form.processing"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        @click="submit"
                    >
                        Créer mon club
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConvocationSheet from '@/Components/Convocation/ConvocationSheet.vue';

const props = defineProps({ event: Object, players: Array });
</script>

<template>
    <Head :title="`Convocations — ${event.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('coach.events')" class="text-gray-400 transition hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Convocations</h2>
                    <p class="text-xs text-gray-500">{{ event.title }} · {{ event.team_name }} · {{ new Date(event.start_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
            </div>
        </template>

        <ConvocationSheet :players="players" :store-route="route('coach.convocations.store', event.id)" />
    </AuthenticatedLayout>
</template>

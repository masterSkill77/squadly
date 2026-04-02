<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    account_type: 'club',
});

const isOrganizer = computed(() => form.account_type === 'organizer');

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Inscription" />

    <div class="relative h-screen w-screen overflow-hidden bg-white">
        <!--
            Two background layers (each 50vw wide), positioned absolutely.
            Club image = left by default, slides right when organizer.
            Organizer image = right by default, slides left when organizer.
        -->

        <!-- Club image (left → exits left when organizer) -->
        <div
            class="absolute inset-y-0 hidden w-1/2 overflow-hidden transition-transform duration-700 ease-in-out lg:block"
            :class="isOrganizer ? '-translate-x-full' : 'translate-x-0'"
            style="left: 0"
        >
            <img
                src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=1200&h=1600&fit=crop"
                alt="Groupe de sportifs souriants"
                class="h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/70 to-emerald-800/30"></div>
            <div class="absolute bottom-12 left-12 right-12 text-white">
                <h2 class="text-3xl font-bold">Créez votre club en quelques minutes.</h2>
                <p class="mt-3 text-emerald-100">Invitez vos coachs et joueurs, et commencez à organiser vos saisons.</p>
            </div>
        </div>

        <!-- Organizer image (right → exits right when club, enters from right when organizer) -->
        <div
            class="absolute inset-y-0 hidden w-1/2 overflow-hidden transition-transform duration-700 ease-in-out lg:block"
            :class="isOrganizer ? 'translate-x-0' : 'translate-x-full'"
            style="right: 0"
        >
            <img
                src="https://images.unsplash.com/photo-1517649763962-0c623066013b?w=1200&h=1600&fit=crop"
                alt="Compétition sportive"
                class="h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-blue-900/70 to-blue-800/30"></div>
            <div class="absolute bottom-12 left-12 right-12 text-white">
                <h2 class="text-3xl font-bold">Organisez vos compétitions facilement.</h2>
                <p class="mt-3 text-blue-100">Championnats, tournois, classements en temps réel — tout est centralisé.</p>
            </div>
        </div>

        <!--
            Form container: occupies the OTHER half.
            Club mode  → form on the RIGHT  (left-1/2)
            Organizer  → form on the LEFT   (left-0)
        -->
        <div
            class="absolute inset-y-0 z-10 flex w-full items-center justify-center overflow-y-auto bg-white px-6 transition-all duration-700 ease-in-out lg:w-1/2"
            :class="isOrganizer ? 'lg:left-0' : 'lg:left-1/2'"
        >
            <div class="w-full max-w-sm py-10">
                <Link href="/" class="mb-8 inline-flex items-center gap-2 text-gray-400 transition hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    <span class="text-sm">Retour à l'accueil</span>
                </Link>

                <Link href="/" class="mb-8 inline-block">
                    <img src="/squadly-logo-light.svg" alt="Squadly" class="h-10" />
                </Link>

                <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
                <p class="mt-1 text-sm text-gray-500 transition-all duration-300">
                    {{ isOrganizer ? 'Organisez vos compétitions sur Squadly.' : 'Lancez votre club sur Squadly.' }}
                </p>

                <!-- Account type selector -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-4 py-3 text-sm font-medium transition-all duration-300"
                        :class="!isOrganizer ? 'border-emerald-500 bg-emerald-50 text-emerald-700 scale-[1.03] shadow-sm' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                        @click="form.account_type = 'club'"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        Club sportif
                    </button>
                    <button
                        type="button"
                        class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-4 py-3 text-sm font-medium transition-all duration-300"
                        :class="isOrganizer ? 'border-blue-500 bg-blue-50 text-blue-700 scale-[1.03] shadow-sm' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                        @click="form.account_type = 'organizer'"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-3.52 1.122 6.023 6.023 0 0 1-3.52-1.122" /></svg>
                        Organisateur
                    </button>
                </div>
                <InputError class="mt-1" :message="form.errors.account_type" />

                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 disabled:opacity-50"
                        :class="isOrganizer ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                    >
                        {{ isOrganizer ? 'Créer mon organisation' : 'Créer mon club' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Déjà inscrit ?
                    <Link :href="route('login')" class="font-medium text-emerald-600 hover:text-emerald-700">Se connecter</Link>
                </p>
            </div>
        </div>
    </div>
</template>

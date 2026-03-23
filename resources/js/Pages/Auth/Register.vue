<script setup>
import AuthImage from '@/Components/Auth/AuthImage.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Inscription" />

    <div class="flex h-screen overflow-hidden">
        <AuthImage
            src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?w=1200&h=1600&fit=crop"
            alt="Groupe de sportifs souriants"
            title="Créez votre club en quelques minutes."
            subtitle="Invitez vos coachs et joueurs, et commencez à organiser vos saisons."
        />

        <!-- Form -->
        <div class="flex w-full items-center justify-center overflow-y-auto px-6 lg:w-1/2">
            <div class="w-full max-w-sm py-10">
                <Link href="/" class="mb-8 inline-flex items-center gap-2 text-gray-400 transition hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    <span class="text-sm">Retour à l'accueil</span>
                </Link>

                <Link href="/" class="mb-8 inline-block">
                    <img src="/squadly-logo-light.svg" alt="Squadly" class="h-10" />
                </Link>

                <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
                <p class="mt-1 text-sm text-gray-500">Lancez votre club sur Squadly.</p>

                <form class="mt-8 space-y-4" @submit.prevent="submit">
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
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    >
                        Créer mon compte
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

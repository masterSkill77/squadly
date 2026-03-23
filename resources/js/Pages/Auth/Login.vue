<script setup>
import AuthImage from '@/Components/Auth/AuthImage.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <div class="flex h-screen overflow-hidden">
        <AuthImage
            src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=1200&h=1600&fit=crop"
            alt="Équipe de football réunie"
            title="Votre club, votre communauté."
            subtitle="Rejoignez des centaines de clubs qui simplifient leur gestion avec Squadly."
        />

        <!-- Form -->
        <div class="flex w-full items-center justify-center px-6 lg:w-1/2">
            <div class="w-full max-w-sm">
                <!-- Logo + retour home -->
                <Link href="/" class="mb-8 inline-flex items-center gap-2 text-gray-400 transition hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    <span class="text-sm">Retour à l'accueil</span>
                </Link>

                <Link href="/" class="mb-8 inline-block">
                    <img src="/squadly-logo-light.svg" alt="Squadly" class="h-10" />
                </Link>

                <h1 class="text-2xl font-bold text-gray-900">Connexion</h1>
                <p class="mt-1 text-sm text-gray-500">Accédez à votre espace club.</p>

                <div v-if="status" class="mt-4 text-sm font-medium text-green-600">{{ status }}</div>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
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
                            autocomplete="current-password"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                            <span class="text-sm text-gray-600">Se souvenir de moi</span>
                        </label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-emerald-600 hover:text-emerald-700">
                            Mot de passe oublié ?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    >
                        Se connecter
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Pas encore de compte ?
                    <Link :href="route('register')" class="font-medium text-emerald-600 hover:text-emerald-700">Créer mon club</Link>
                </p>
            </div>
        </div>
    </div>
</template>

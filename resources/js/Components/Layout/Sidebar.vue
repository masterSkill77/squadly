<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarLink from './SidebarLink.vue';
import { Role } from '@/Utils/roles';

defineProps({ collapsed: Boolean });
defineEmits(['toggle']);

const page = usePage();
const role = page.props.auth.role;
const userName = page.props.auth.user.name;
const userInitial = userName.charAt(0).toUpperCase();
const isAdmin = role === Role.Admin;
const isCoach = role === Role.Coach;

const showProfileMenu = ref(false);
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-gray-200 bg-white transition-all duration-300"
        :class="collapsed ? 'w-16' : 'w-60'"
    >
        <!-- Logo + Collapse -->
        <div class="flex h-16 items-center justify-between border-b border-gray-100 px-4">
            <Link :href="route('dashboard')" class="flex items-center gap-2 overflow-hidden">
                <img src="/squadly-icon-square.svg" alt="Squadly" class="h-8 w-8 shrink-0" />
                <span v-if="!collapsed" class="text-lg font-bold text-gray-900 transition-opacity">Squadly</span>
            </Link>
            <button
                class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                @click="$emit('toggle')"
            >
                <svg class="h-4 w-4 transition-transform" :class="collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                </svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-4">
            <SidebarLink :href="route('dashboard')" :active="route().current('dashboard')" :collapsed="collapsed">
                <template #icon>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                </template>
                Tableau de bord
            </SidebarLink>

            <!-- Admin only -->
            <template v-if="isAdmin">
                <p v-if="!collapsed" class="mt-6 mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Club</p>

                <SidebarLink :href="route('members.index')" :active="route().current('members.*')" :collapsed="collapsed">
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    </template>
                    Membres
                </SidebarLink>

                <SidebarLink :href="route('club.show')" :active="route().current('club.show')" :collapsed="collapsed">
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </template>
                    Mon club
                </SidebarLink>
            </template>

            <!-- Coach only -->
            <template v-if="isCoach">
                <p v-if="!collapsed" class="mt-6 mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Mes équipes</p>

                <SidebarLink :href="route('coach.effectifs')" :active="route().current('coach.effectifs') || route().current('coach.team*')" :collapsed="collapsed">
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                    </template>
                    Effectifs
                </SidebarLink>
            </template>

            <!-- Shared: Planning -->
            <p v-if="!collapsed" class="mt-6 mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Planning</p>

            <SidebarLink :href="isAdmin ? route('events.index') : isCoach ? route('coach.events') : '#'" :active="route().current('events.*') || route().current('coach.events*')" :collapsed="collapsed">
                <template #icon>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                </template>
                Événements
            </SidebarLink>

            <SidebarLink v-if="isAdmin || isCoach" :href="isAdmin ? route('attendance.index') : route('coach.attendance')" :active="route().current('attendance.*') || route().current('coach.attendance*')" :collapsed="collapsed">
                <template #icon>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                </template>
                Présences
            </SidebarLink>

            <SidebarLink v-if="!isAdmin && !isCoach" :href="route('membre.convocations')" :active="route().current('membre.convocations*')" :collapsed="collapsed">
                <template #icon>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                </template>
                Convocations
            </SidebarLink>

            <!-- Admin only: Documents -->
            <template v-if="isAdmin">
                <p v-if="!collapsed" class="mt-6 mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Autre</p>

                <SidebarLink href="#" :active="false" :collapsed="collapsed">
                    <template #icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </template>
                    Documents
                </SidebarLink>
            </template>
        </nav>

        <!-- Profile -->
        <div class="border-t border-gray-100 p-2">
            <div class="relative">
                <button
                    class="flex w-full items-center gap-3 rounded-lg p-2 text-left transition hover:bg-gray-100"
                    :class="collapsed ? 'justify-center' : ''"
                    @click="showProfileMenu = !showProfileMenu"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ userInitial }}</span>
                    <template v-if="!collapsed">
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700">{{ userName }}</span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="showProfileMenu ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                    </template>
                </button>

                <div v-if="showProfileMenu" class="fixed inset-0 z-40" @click="showProfileMenu = false" />
                <Transition
                    enter-active-class="transition ease-out duration-150"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="showProfileMenu" class="absolute bottom-full left-0 z-50 mb-1 w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                        <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50" @click="showProfileMenu = false">Mon profil</Link>
                        <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50" @click="showProfileMenu = false">Déconnexion</Link>
                    </div>
                </Transition>
            </div>
        </div>
    </aside>
</template>

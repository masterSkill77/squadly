<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import TopBar from '@/Components/Layout/TopBar.vue';
import Toast from '@/Components/Layout/Toast.vue';

const STORAGE_KEY = 'squadly_sidebar_collapsed';
const collapsed = ref(localStorage.getItem(STORAGE_KEY) === 'true');
const mobileOpen = ref(false);

function toggleSidebar() {
    collapsed.value = !collapsed.value;
    localStorage.setItem(STORAGE_KEY, collapsed.value);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-20 bg-black/30 lg:hidden"
            @click="mobileOpen = false"
        />

        <!-- Sidebar (desktop: always visible, mobile: overlay) -->
        <div class="hidden lg:block">
            <Sidebar :collapsed="collapsed" @toggle="toggleSidebar" />
        </div>
        <div v-if="mobileOpen" class="fixed inset-y-0 left-0 z-30 lg:hidden">
            <Sidebar :collapsed="false" @toggle="mobileOpen = false" />
        </div>

        <!-- Main area -->
        <div class="transition-all duration-300" :class="collapsed ? 'lg:pl-16' : 'lg:pl-60'">
            <TopBar>
                <template #left>
                    <div class="flex items-center gap-4">
                        <!-- Mobile hamburger -->
                        <button class="text-gray-400 hover:text-gray-600 lg:hidden" @click="mobileOpen = true">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        </button>
                        <slot name="header" />
                    </div>
                </template>
            </TopBar>

            <main class="p-6">
                <slot />
            </main>
        </div>

        <Toast />
    </div>
</template>

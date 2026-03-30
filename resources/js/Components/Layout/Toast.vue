<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timeout = null;

function show(msg, t = 'success') {
    message.value = msg;
    type.value = t;
    visible.value = true;
    clearTimeout(timeout);
    timeout = setTimeout(() => { visible.value = false; }, 3000);
}

function check() {
    if (page.props.flash?.success) show(page.props.flash.success, 'success');
    else if (page.props.flash?.error) show(page.props.flash.error, 'error');
}

onMounted(check);
watch(() => page.props.flash, check, { deep: true });
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl px-5 py-3 text-sm font-medium shadow-lg"
            :class="type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
        >
            <svg v-if="type === 'success'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            {{ message }}
            <button class="ml-2 opacity-70 hover:opacity-100" @click="visible = false">&times;</button>
        </div>
    </Transition>
</template>

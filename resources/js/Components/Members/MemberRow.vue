<script setup>
import { Link } from '@inertiajs/vue3';
import { Role, roleLabels } from '@/Utils/roles';

defineProps({ member: Object });
</script>

<template>
    <Link :href="route('members.show', member.id)" class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 transition hover:shadow-md">
        <div v-if="member.photo_url" class="h-10 w-10 overflow-hidden rounded-full">
            <img :src="member.photo_url" :alt="member.full_name" class="h-full w-full object-cover" />
        </div>
        <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
            {{ member.first_name.charAt(0) }}{{ member.last_name.charAt(0) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ member.full_name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ member.email }}</p>
        </div>
        <span
            class="hidden rounded-full px-2.5 py-0.5 text-xs font-medium sm:inline-block"
            :class="member.role === Role.Coach ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'"
        >
            {{ roleLabels[member.role] || member.role }}
        </span>
    </Link>
</template>

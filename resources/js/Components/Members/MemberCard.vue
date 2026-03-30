<script setup>
import { Link } from '@inertiajs/vue3';
import { Role, roleLabels } from '@/Utils/roles';

defineProps({ member: Object });
</script>

<template>
    <Link :href="route('members.show', member.id)" class="group rounded-xl border border-gray-100 bg-white p-5 text-center transition hover:border-emerald-200 hover:shadow-md">
        <div v-if="member.photo_url" class="mx-auto h-14 w-14 overflow-hidden rounded-full">
            <img :src="member.photo_url" :alt="member.full_name" class="h-full w-full object-cover" />
        </div>
        <div v-else class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
            {{ member.first_name.charAt(0) }}{{ member.last_name.charAt(0) }}
        </div>
        <p class="mt-3 text-sm font-semibold text-gray-900 truncate group-hover:text-emerald-600 transition">{{ member.full_name }}</p>
        <p class="mt-0.5 text-xs text-gray-500 truncate">{{ member.email }}</p>
        <span
            class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-medium"
            :class="member.role === Role.Coach ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'"
        >
            {{ roleLabels[member.role] || member.role }}
        </span>
    </Link>
</template>

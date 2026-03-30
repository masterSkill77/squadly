<script setup>
const props = defineProps({ attendance: Object });
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white p-6">
        <h4 class="text-sm font-semibold text-gray-900">Présences</h4>

        <template v-if="attendance.total > 0">
            <!-- Rate -->
            <div class="mt-4 flex items-center gap-3">
                <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full rounded-full transition-all"
                        :class="attendance.rate >= 75 ? 'bg-emerald-500' : attendance.rate >= 50 ? 'bg-amber-500' : 'bg-red-500'"
                        :style="{ width: attendance.rate + '%' }"
                    />
                </div>
                <span class="text-lg font-bold" :class="attendance.rate >= 75 ? 'text-emerald-600' : attendance.rate >= 50 ? 'text-amber-600' : 'text-red-600'">{{ attendance.rate }}%</span>
            </div>

            <!-- Breakdown -->
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg bg-emerald-50 p-2">
                    <p class="text-lg font-bold text-emerald-700">{{ attendance.present }}</p>
                    <p class="text-[10px] text-emerald-600">Présent{{ attendance.present > 1 ? 's' : '' }}</p>
                </div>
                <div class="rounded-lg bg-red-50 p-2">
                    <p class="text-lg font-bold text-red-700">{{ attendance.absent }}</p>
                    <p class="text-[10px] text-red-600">Absent{{ attendance.absent > 1 ? 's' : '' }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-2">
                    <p class="text-lg font-bold text-amber-700">{{ attendance.justified }}</p>
                    <p class="text-[10px] text-amber-600">Justifié{{ attendance.justified > 1 ? 's' : '' }}</p>
                </div>
            </div>

            <p class="mt-3 text-center text-[10px] text-gray-400">Sur {{ attendance.total }} événement{{ attendance.total > 1 ? 's' : '' }}</p>
        </template>

        <p v-else class="mt-3 text-sm text-gray-400">Aucune donnée de présence.</p>
    </div>
</template>

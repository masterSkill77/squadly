<script setup>
import BracketMatchCard from './BracketMatchCard.vue';

defineOptions({ name: 'BracketNode' });

const props = defineProps({
    node: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center">
        <!-- Feeder subtrees -->
        <template v-if="node.children.length">
            <div class="flex flex-col gap-3">
                <BracketNode v-for="(child, i) in node.children" :key="i" :node="child" />
            </div>

            <!-- Bracket connector: ┐ + ┘ → ─ -->
            <div class="flex flex-col self-stretch" style="width: 24px; min-height: 0">
                <div class="flex-1 border-b-2 border-r-2 border-gray-200" style="border-bottom-right-radius: 6px" />
                <div class="flex-1 border-t-2 border-r-2 border-gray-200" style="border-top-right-radius: 6px" />
            </div>
            <div class="border-t-2 border-gray-200" style="width: 16px" />
        </template>

        <!-- Match card -->
        <BracketMatchCard :game="node.game" />
    </div>
</template>

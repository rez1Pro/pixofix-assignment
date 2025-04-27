<script setup lang="ts">
defineProps<{
    percentage: number;
    total: number;
    completed: number;
    isRefreshing: boolean;
}>();

defineEmits<{
    refresh: [];
}>();
</script>

<template>
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Order Progress</h3>
            <button @click="$emit('refresh')" class="flex items-center text-sm text-gray-600 hover:text-gray-900">
                <svg :class="{ 'animate-spin': isRefreshing }" class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-emerald-600 h-2.5 rounded-full" :style="{ width: `${percentage}%` }"></div>
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-600">
            <span>{{ percentage }}% Complete</span>
            <span>{{ completed }} / {{ total }} Files</span>
        </div>
    </section>
</template>
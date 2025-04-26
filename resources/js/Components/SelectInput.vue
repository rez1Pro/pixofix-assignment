<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string | number>({ required: true });

const select = ref<HTMLSelectElement | null>(null);

onMounted(() => {
    if (select.value?.hasAttribute('autofocus')) {
        select.value?.focus();
    }
});

defineExpose({ focus: () => select.value?.focus() });
</script>

<template>
    <select
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        v-model="model" ref="select">
        <slot></slot>
    </select>
</template>
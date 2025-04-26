<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline';

const isDark = ref(false);

onMounted(() => {
    // Check initial theme
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

const toggleTheme = () => {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
};
</script>

<template>
    <button
        @click="toggleTheme"
        type="button"
        class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <SunIcon v-if="!isDark" class="h-5 w-5 text-gray-600 dark:text-gray-400" />
        <MoonIcon v-else class="h-5 w-5 text-gray-600 dark:text-gray-400" />
    </button>
</template>

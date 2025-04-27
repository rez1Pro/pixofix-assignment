<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const appName = computed(() => {
    return window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
});
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <Link href="/" class="flex items-center gap-2">
                <ApplicationLogo class="h-10 w-10 text-emerald-600" />
                <span class="text-xl font-bold text-gray-900">{{ appName }}</span>
                </Link>
            </div>

            <!-- Content Card -->
            <div class="bg-white p-8 rounded-lg shadow-sm">
                <slot />
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>© {{ new Date().getFullYear() }} {{ appName }}</p>
            </div>
        </div>
    </div>
</template>

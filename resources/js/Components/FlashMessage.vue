<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const page = usePage();

const success = ref("");
const error = ref("");

watch(() => page.props.flash, (value: any) => {
    success.value = value?.success || "";
    error.value = value?.error || "";
}, { deep: true });

const showMessage = ref(true);

const hideMessage = () => {
    showMessage.value = false;
};

// Reset timer when flash message changes
watch(() => success.value || error.value, () => {
    showMessage.value = true;
    setTimeout(hideMessage, 3000);
}, { deep: true });

onMounted(() => {
    setTimeout(hideMessage, 3000);
});
</script>

<template>
    <div v-if="(success || error) && showMessage"
        class="fixed top-0 right-0 z-50 max-w-md transition-all duration-300 ease-in-out"
        :class="{ 'animate-slide-in-right': showMessage, 'opacity-0 translate-x-full': !showMessage }">
        <div v-if="success" class="animate-slide-up backdrop-blur-md bg-white/30 dark:bg-gray-900/30
                   border border-emerald-200/50 dark:border-emerald-800/50
                   shadow-lg shadow-emerald-100/20 dark:shadow-emerald-900/20
                   rounded-2xl overflow-hidden flex items-center gap-3
                   transform transition-all duration-500 ease-in-out hover:translate-y-[-2px]
                   translate-x-0">
            <div class="bg-emerald-500 dark:bg-emerald-600 h-full w-1.5"></div>
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="bg-emerald-100 dark:bg-emerald-900/50 p-2 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ success }}</span>
            </div>
            <button @click="showMessage = false" class="self-start p-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300
                       transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>

        <div v-if="error" class="animate-slide-up backdrop-blur-md bg-white/30 dark:bg-gray-900/30
                   border border-rose-200/50 dark:border-rose-800/50 
                   shadow-lg shadow-rose-100/20 dark:shadow-rose-900/20
                   rounded-2xl overflow-hidden flex items-center gap-3
                   transform transition-all duration-500 ease-in-out hover:translate-y-[-2px]
                   translate-x-0">
            <div class="bg-rose-500 dark:bg-rose-600 h-full w-1.5"></div>
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="bg-rose-100 dark:bg-rose-900/50 p-2 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600 dark:text-rose-400"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ error }}</span>
            </div>
            <button @click="showMessage = false" class="self-start p-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300
                       transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>
    </div>
</template>
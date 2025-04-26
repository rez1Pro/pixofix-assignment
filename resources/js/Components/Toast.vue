<script setup lang="ts">
import { ref, watchEffect } from 'vue';

interface Props {
    message?: string;
    type?: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
    show?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    message: '',
    type: 'info',
    duration: 3000,
    show: false
});

const emit = defineEmits(['close']);

const isVisible = ref(props.show);

const close = () => {
    isVisible.value = false;
    emit('close');
};

// Auto-close timer
watchEffect(() => {
    if (props.show && props.duration > 0) {
        isVisible.value = true;
        const timer = setTimeout(() => {
            close();
        }, props.duration);

        return () => clearTimeout(timer);
    }
});

// Reset visibility when show prop changes
watchEffect(() => {
    isVisible.value = props.show;
});

// Determine toast color based on type
const toastClasses = {
    success: 'bg-green-100 border-green-400 text-green-800 dark:bg-green-900/50 dark:border-green-600 dark:text-green-200',
    error: 'bg-red-100 border-red-400 text-red-800 dark:bg-red-900/50 dark:border-red-600 dark:text-red-200',
    warning: 'bg-yellow-100 border-yellow-400 text-yellow-800 dark:bg-yellow-900/50 dark:border-yellow-600 dark:text-yellow-200',
    info: 'bg-blue-100 border-blue-400 text-blue-800 dark:bg-blue-900/50 dark:border-blue-600 dark:text-blue-200'
};

const iconClasses = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info: 'text-blue-600 dark:text-blue-400'
};
</script>

<template>
    <Transition enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isVisible" class="fixed top-4 right-4 z-50 max-w-sm">
            <div class="rounded-lg shadow-md border-l-4 px-4 py-3" :class="toastClasses[type]">
                <div class="flex items-center">
                    <!-- Success Icon -->
                    <svg v-if="type === 'success'" class="h-5 w-5 mr-3" :class="iconClasses[type]"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>

                    <!-- Error Icon -->
                    <svg v-if="type === 'error'" class="h-5 w-5 mr-3" :class="iconClasses[type]"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>

                    <!-- Warning Icon -->
                    <svg v-if="type === 'warning'" class="h-5 w-5 mr-3" :class="iconClasses[type]"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>

                    <!-- Info Icon -->
                    <svg v-if="type === 'info'" class="h-5 w-5 mr-3" :class="iconClasses[type]"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>

                    <div>
                        <p class="font-medium">{{ message }}</p>
                    </div>

                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button @click="close" class="inline-flex rounded-md p-1.5"
                                :class="[iconClasses[type], 'hover:bg-opacity-20 hover:bg-gray-400']">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowUpTrayIcon,
    FolderIcon,
    PaperClipIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    files: [],
});

const fileInputRef = ref(null);
const filePreviewUrls = ref([]);
const dragOver = ref(false);

const submitForm = () => {
    form.post('/orders', {
        preserveScroll: true,
        onSuccess: () => {
            // Clear the form after successful submission
            form.reset();
            filePreviewUrls.value = [];
        },
    });
};

const addFiles = (files) => {
    if (!files || files.length === 0) return;

    const newFiles = [...form.files];
    const newPreviewUrls = [...filePreviewUrls.value];

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            newFiles.push(file);
            newPreviewUrls.push({
                url: URL.createObjectURL(file),
                name: file.name,
                size: formatFileSize(file.size),
                index: newFiles.length - 1,
            });
        }
    }

    form.files = newFiles;
    filePreviewUrls.value = newPreviewUrls;
};

const handleFileSelect = (event) => {
    const files = event.target.files;
    addFiles(files);
};

const handleFileDrop = (event) => {
    event.preventDefault();
    dragOver.value = false;
    const files = event.dataTransfer.files;
    addFiles(files);
};

const handleDragOver = (event) => {
    event.preventDefault();
    dragOver.value = true;
};

const handleDragLeave = () => {
    dragOver.value = false;
};

const removeFile = (index) => {
    const newFiles = [...form.files];
    newFiles.splice(index, 1);
    form.files = newFiles;

    const newPreviewUrls = filePreviewUrls.value.filter(item => item.index !== index);
    newPreviewUrls.forEach((item, i) => {
        if (item.index > index) {
            item.index -= 1;
        }
    });
    filePreviewUrls.value = newPreviewUrls;
};

const triggerFileInput = () => {
    fileInputRef.value.click();
};

const formatFileSize = (size) => {
    if (size < 1024) {
        return size + ' B';
    } else if (size < 1024 * 1024) {
        return (size / 1024).toFixed(2) + ' KB';
    } else {
        return (size / (1024 * 1024)).toFixed(2) + ' MB';
    }
};
</script>

<template>
    <AuthenticatedLayout header="Create Order" description="Create a new order with files">

        <Head title="Create Order" />

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="submitForm" class="space-y-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow px-4 py-5 sm:p-6">
                    <div class="space-y-6">
                        <!-- Order Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Order Name
                            </label>
                            <div class="mt-1">
                                <input id="name" v-model="form.name" type="text" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter order name" />
                            </div>
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Order Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <div class="mt-1">
                                <textarea id="description" v-model="form.description" rows="3"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter order description"></textarea>
                            </div>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Upload Files
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md"
                                :class="{ 'border-indigo-500 dark:border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': dragOver }"
                                @dragover="handleDragOver" @dragleave="handleDragLeave" @drop="handleFileDrop">
                                <div class="space-y-1 text-center">
                                    <div class="flex flex-col items-center">
                                        <FolderIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                            <label for="file-upload"
                                                class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                                <span @click="triggerFileInput">Upload files</span>
                                                <input ref="fileInputRef" id="file-upload" name="file-upload"
                                                    type="file" multiple class="sr-only" @change="handleFileSelect"
                                                    accept="image/*" />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            PNG, JPG, GIF up to 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.files" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.files }}
                            </p>

                            <!-- File Preview -->
                            <div v-if="filePreviewUrls.length > 0" class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Selected Files ({{ filePreviewUrls.length }})
                                </h4>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <li v-for="(preview, i) in filePreviewUrls" :key="i"
                                        class="relative rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden group">
                                        <div
                                            class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                            <PaperClipIcon class="h-4 w-4 text-gray-500 dark:text-gray-400 mr-2" />
                                            <div class="text-xs text-gray-700 dark:text-gray-300 truncate flex-1">
                                                {{ preview.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                {{ preview.size }}
                                            </div>
                                            <button type="button" @click="removeFile(preview.index)"
                                                class="ml-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 focus:outline-none">
                                                <XMarkIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <div class="h-32 bg-gray-100 dark:bg-gray-800">
                                            <img :src="preview.url" alt="Preview"
                                                class="h-full w-full object-contain" />
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-x-3">
                    <a href="/orders"
                        class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex justify-center items-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-indigo-500 dark:hover:bg-indigo-600">
                        <ArrowUpTrayIcon v-if="form.processing" class="animate-bounce -ml-1 mr-2 h-5 w-5" />
                        {{ form.processing ? 'Uploading...' : 'Create Order' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
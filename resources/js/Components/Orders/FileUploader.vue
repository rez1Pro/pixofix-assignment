<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Use WebFile to distinguish from app's File interface
interface WebFile extends File {
    // Extending native File interface
}

const props = defineProps<{
    targetFolder: string;
    targetSubfolder?: string | null;
    showModal: boolean;
}>();

const emit = defineEmits<{
    close: [];
    upload: [files: WebFile[], targetFolder: string, targetSubfolder?: string | null];
}>();

const dropZone = ref<HTMLElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const files = ref<WebFile[]>([]);
const uploadProgress = ref<Record<string, number>>({});
const isUploading = ref(false);

// Handle file selection
const handleFileSelection = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        addFiles(Array.from(input.files));
    }
};

// Handle drop event
const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
    isDragging.value = false;

    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

// Add files to the list
const addFiles = (newFiles: WebFile[]) => {
    // Filter for only image files
    const imageFiles = newFiles.filter(file =>
        file.type.startsWith('image/') ||
        file.type === 'application/pdf' ||
        file.type === 'application/msword' ||
        file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    );

    // Add files to the list
    files.value = [...files.value, ...imageFiles];

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Remove a file from the list
const removeFile = (index: number) => {
    files.value.splice(index, 1);
};

// Trigger file browser
const triggerFileBrowser = () => {
    fileInput.value?.click();
};

// Handle drag events
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
    isDragging.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
    isDragging.value = false;
};

// Upload files
const uploadFiles = () => {
    if (files.value.length === 0) return;

    isUploading.value = true;

    // In a real application, you would upload files to a server here
    // Simulating upload progress for each file
    files.value.forEach((file, index) => {
        uploadProgress.value[index] = 0;

        // Simulate progress
        const interval = setInterval(() => {
            uploadProgress.value[index] += 10;

            if (uploadProgress.value[index] >= 100) {
                clearInterval(interval);

                // When all files are uploaded
                if (Object.values(uploadProgress.value).every(progress => progress >= 100)) {
                    // Emit upload event with all files
                    emit('upload', files.value, props.targetFolder, props.targetSubfolder);

                    // Reset state
                    setTimeout(() => {
                        files.value = [];
                        uploadProgress.value = {};
                        isUploading.value = false;
                        emit('close');
                    }, 500);
                }
            }
        }, 200);
    });
};

// Get file size in human-readable format
const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) {
        return bytes + ' B';
    } else if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }
};

// Setup drag and drop event listeners
onMounted(() => {
    if (dropZone.value) {
        dropZone.value.addEventListener('dragover', handleDragOver);
        dropZone.value.addEventListener('dragleave', handleDragLeave);
        dropZone.value.addEventListener('drop', handleDrop);
    }
});

// Cleanup event listeners
onBeforeUnmount(() => {
    if (dropZone.value) {
        dropZone.value.removeEventListener('dragover', handleDragOver);
        dropZone.value.removeEventListener('dragleave', handleDragLeave);
        dropZone.value.removeEventListener('drop', handleDrop);
    }
});
</script>

<template>
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Upload Files</h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Target folder:</span> {{ targetFolder }}
                    <span v-if="targetSubfolder"> / {{ targetSubfolder }}</span>
                </p>
            </div>

            <!-- Dropzone -->
            <div ref="dropZone" :class="[
                'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors',
                isDragging ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300 hover:border-emerald-400'
            ]" @click="triggerFileBrowser">
                <input ref="fileInput" type="file" multiple class="hidden" accept="image/*,.pdf,.doc,.docx"
                    @change="handleFileSelection" />

                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-medium text-emerald-600">Click to upload</span> or drag and drop
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Supported files: Images, PDFs, Word documents
                </p>
            </div>

            <!-- File list -->
            <ul v-if="files.length > 0" class="mt-4 space-y-2 max-h-60 overflow-y-auto">
                <li v-for="(file, index) in files" :key="index" class="flex items-center p-2 bg-gray-50 rounded-md">
                    <div class="flex-shrink-0 mr-2">
                        <svg v-if="file.type.startsWith('image/')" class="h-5 w-5 text-emerald-500" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                    </div>

                    <!-- Progress bar or remove button -->
                    <div v-if="isUploading" class="ml-4 w-24">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full"
                                :style="{ width: `${uploadProgress[index] || 0}%` }"></div>
                        </div>
                        <p class="text-xs text-right mt-1">{{ uploadProgress[index] || 0 }}%</p>
                    </div>
                    <button v-else @click.stop="removeFile(index)"
                        class="ml-4 flex-shrink-0 text-red-500 hover:text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </li>
            </ul>

            <!-- Actions -->
            <div class="mt-6 flex justify-end space-x-3">
                <button @click="$emit('close')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    :disabled="isUploading">
                    Cancel
                </button>
                <button @click="uploadFiles"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    :disabled="files.length === 0 || isUploading">
                    {{ isUploading ? 'Uploading...' : 'Upload Files' }}
                </button>
            </div>
        </div>
    </div>
</template>
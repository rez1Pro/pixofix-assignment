<script setup>
import Modal from '@/Components/Modal.vue';
import Toast from '@/Components/Toast.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    filesGrouped: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['filesUpdated']);

// State management
const groupedFiles = ref({ ...props.filesGrouped });
const showDeleteModal = ref(false);
const fileToDelete = ref(null);
const selectedFiles = ref([]);
const showClaimModal = ref(false);

// Forms
const uploadForm = useForm({ files: [] });
const deleteForm = useForm();
const claimForm = useForm({ fileIds: [] });

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'info',
    duration: 5000
});

// Show toast notification
const showToast = (message, type = 'info', duration = 5000) => {
    toast.value = {
        show: true,
        message,
        type,
        duration
    };
};

// Close toast notification
const closeToast = () => {
    toast.value.show = false;
};

// Computed properties
const hasFiles = computed(() => Object.keys(groupedFiles.value).length > 0);
const hasSelectedFiles = computed(() => selectedFiles.value.length > 0);
const allFilesSelected = computed(() => {
    let totalFiles = 0;
    for (const directory in groupedFiles.value) {
        totalFiles += groupedFiles.value[directory].length;
    }
    return totalFiles > 0 && selectedFiles.value.length === totalFiles;
});

// Methods
const formatFileSize = (bytes) => {
    if (bytes === undefined || bytes === null || isNaN(bytes) || bytes < 0) {
        return '0 Bytes';
    }

    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    if (!dateString) return '';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid date';

        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(date);
    } catch (error) {
        return 'Invalid date';
    }
};

const isImageFile = (file) => file.mime_type?.startsWith('image/');

// Selection methods
const toggleSelectFile = (fileId) => {
    const index = selectedFiles.value.indexOf(fileId);
    if (index === -1) {
        selectedFiles.value.push(fileId);
    } else {
        selectedFiles.value.splice(index, 1);
    }
};

const toggleSelectAll = () => {
    if (allFilesSelected.value) {
        selectedFiles.value = [];
    } else {
        selectedFiles.value = [];
        for (const directory in groupedFiles.value) {
            groupedFiles.value[directory].forEach(file => {
                selectedFiles.value.push(file.id);
            });
        }
    }
};

const selectAllInDirectory = (directory) => {
    const directoryFiles = groupedFiles.value[directory];
    const allSelected = directoryFiles.every(file => selectedFiles.value.includes(file.id));

    if (allSelected) {
        directoryFiles.forEach(file => {
            const index = selectedFiles.value.indexOf(file.id);
            if (index !== -1) {
                selectedFiles.value.splice(index, 1);
            }
        });
    } else {
        directoryFiles.forEach(file => {
            if (!selectedFiles.value.includes(file.id)) {
                selectedFiles.value.push(file.id);
            }
        });
    }
};

// File handling methods
const handleFileUpload = (event) => {
    const files = event.target.files;
    if (!files.length) return;

    uploadForm.files = files;

    uploadForm.post(route('files.store', props.order.id), {
        forceFormData: true,
        onSuccess: () => {
            router.reload({ only: ['filesGrouped', 'stats'] });
            emit('filesUpdated');
            showToast('Files uploaded successfully', 'success');
            event.target.value = null;
        },
        onError: (errors) => {
            showToast(errors.message || 'Failed to upload files', 'error');
            event.target.value = null;
        }
    });
};

const downloadFile = (file) => {
    window.location.href = route('files.download', file.id);
};

const downloadAllFiles = () => {
    // Using form since route() doesn't handle this specific endpoint
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = route('orders.show', props.order.id) + '/files/download';

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

const downloadSelectedFiles = () => {
    if (selectedFiles.value.length === 0) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('files.download-selected', props.order.id);

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    // Add file IDs
    selectedFiles.value.forEach((fileId, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `fileIds[${index}]`;
        input.value = fileId;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

const downloadDirectory = (directory) => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('orders.show', props.order.id) + '/directory/download';

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    // Add directory parameter
    const directoryInput = document.createElement('input');
    directoryInput.type = 'hidden';
    directoryInput.name = 'directory';
    directoryInput.value = directory;
    form.appendChild(directoryInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

const promptDeleteFile = (file) => {
    fileToDelete.value = file;
    showDeleteModal.value = true;
};

const deleteFile = () => {
    if (!fileToDelete.value) return;

    deleteForm.delete(route('files.destroy', fileToDelete.value.id), {
        onSuccess: () => {
            router.reload({ only: ['filesGrouped', 'stats'] });
            emit('filesUpdated');
            showToast('File deleted successfully', 'success');
            showDeleteModal.value = false;
            fileToDelete.value = null;
        },
        onError: () => {
            showToast('Failed to delete file', 'error');
            fileToDelete.value = null;
        }
    });
};

const claimSelectedFiles = () => {
    if (selectedFiles.value.length === 0) return;

    claimForm.fileIds = [...selectedFiles.value];

    claimForm.post(route('claims.claim-files', props.order.id), {
        onSuccess: () => {
            router.reload({ only: ['filesGrouped', 'stats'] });
            emit('filesUpdated');
            showToast('Files claimed successfully', 'success');
            showClaimModal.value = false;
            selectedFiles.value = [];
        },
        onError: () => {
            showToast('Failed to claim files', 'error');
        }
    });
};

const promptClaimFiles = () => {
    if (selectedFiles.value.length === 0) {
        showToast('Please select at least one file to claim', 'warning');
        return;
    }
    showClaimModal.value = true;
};
</script>
<template>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Toast notification -->
        <Toast :show="toast.show" :message="toast.message" :type="toast.type" :duration="toast.duration"
            @close="closeToast" />
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">File Manager</h3>
            <div class="flex space-x-2">
                <!-- Upload Button -->
                <label
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer"
                    :class="{ 'opacity-50 cursor-not-allowed': uploadForm.processing }"
                    :disabled="uploadForm.processing">
                    <span v-if="uploadForm.processing">Uploading...</span>
                    <span v-else>Upload Files</span>
                    <input type="file" class="hidden" multiple @change="handleFileUpload"
                        :disabled="uploadForm.processing" />
                </label>

                <!-- Download All Button -->
                <button @click="downloadAllFiles"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="!hasFiles" :class="{ 'opacity-50 cursor-not-allowed': !hasFiles }">
                    Download All
                </button>
            </div>
        </div>

        <!-- Selected files action bar -->
        <div v-if="hasSelectedFiles" class="bg-gray-50 p-3 flex justify-between items-center border-b">
            <div class="text-sm text-gray-700">
                {{ selectedFiles.length }} file(s) selected
            </div>
            <div class="flex space-x-2">
                <button @click="downloadSelectedFiles"
                    class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-green-700 focus:bg-green-700 transition">
                    Download Selected
                </button>

                <button @click="promptClaimFiles"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700 focus:bg-blue-700 transition"
                    :disabled="claimForm.processing" :class="{ 'opacity-50 cursor-not-allowed': claimForm.processing }">
                    <span v-if="claimForm.processing">Claiming...</span>
                    <span v-else>Claim Selected</span>
                </button>
            </div>
        </div>

        <!-- File listing section -->
        <div class="p-4" v-if="hasFiles">
            <div>
                <!-- Select all checkbox -->
                <div class="mb-4 flex items-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" :checked="allFilesSelected" @change="toggleSelectAll"
                            class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Select All Files</span>
                    </label>
                </div>

                <div v-for="(files, directory) in groupedFiles" :key="directory" class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <label class="inline-flex items-center cursor-pointer mr-2">
                                <input type="checkbox" :checked="files.every(file => selectedFiles.includes(file.id))"
                                    @change="selectAllInDirectory(directory)"
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            </label>
                            <h4 class="text-md font-medium text-gray-700">
                                {{ directory === '' ? 'Root Directory' : directory }}
                                <span class="ml-2 text-sm text-gray-500">
                                    ({{ files.length }} {{ files.length === 1 ? 'file' : 'files' }})
                                </span>
                            </h4>
                        </div>
                        <button @click="downloadDirectory(directory)"
                            class="text-sm px-3 py-1 bg-green-100 text-green-800 rounded-md hover:bg-green-200 transition">
                            Download Directory
                        </button>
                    </div>

                    <ul class="divide-y divide-gray-200 border rounded-lg">
                        <li v-for="file in files" :key="file.id"
                            class="flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-gray-50">
                            <div class="flex items-center space-x-3 mb-2 sm:mb-0">
                                <!-- Checkbox for selection -->
                                <div class="flex-shrink-0">
                                    <input type="checkbox" :checked="selectedFiles.includes(file.id)"
                                        @change="toggleSelectFile(file.id)"
                                        class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                </div>

                                <!-- File Type Icon -->
                                <div class="flex-shrink-0">
                                    <svg v-if="isImageFile(file)" class="w-6 h-6 text-blue-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>

                                <!-- File Info -->
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate"
                                        :title="file.original_name || 'Unnamed file'">
                                        {{ file.original_name || 'Unnamed file' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ file.size ? formatFileSize(file.size) : '0 B' }} · {{
                                            formatDate(file.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- File Actions -->
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-gray-100 text-gray-800': file.status === 'pending',
                                        'bg-yellow-100 text-yellow-800': file.status === 'processing',
                                        'bg-green-100 text-green-800': file.status === 'completed',
                                        'bg-blue-100 text-blue-800': file.status === 'claimed',
                                        'bg-red-100 text-red-800': file.status === 'failed'
                                    }">
                                    {{ file.status }}
                                </span>

                                <button @click="downloadFile(file)"
                                    class="text-sm px-2 py-1 text-blue-600 hover:text-blue-800 transition"
                                    title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </button>

                                <button @click="promptDeleteFile(file)"
                                    class="text-sm px-2 py-1 text-red-600 hover:text-red-800 transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <p class="text-gray-600 mb-2">No files have been uploaded yet</p>
            <label
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                Upload Files
                <input type="file" class="hidden" multiple @change="handleFileUpload" />
            </label>
        </div>

        <!-- Delete Confirmation Modal -->
        <modal v-if="showDeleteModal" @close="showDeleteModal = false">
            <template #header>
                <h3 class="text-lg font-medium text-gray-900">Delete File</h3>
            </template>

            <template #content>
                <p>Are you sure you want to delete this file? This action cannot be undone.</p>
                <p v-if="fileToDelete" class="mt-2 text-sm font-medium text-gray-700">{{ fileToDelete.original_name }}
                </p>
            </template>

            <template #footer>
                <button @click="showDeleteModal = false"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancel
                </button>
                <button @click="deleteFile"
                    class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="{ 'opacity-50 cursor-not-allowed': deleteForm.processing }"
                    :disabled="deleteForm.processing">
                    {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                </button>
            </template>
        </modal>

        <!-- Claim Confirmation Modal -->
        <modal v-if="showClaimModal" @close="showClaimModal = false">
            <template #header>
                <h3 class="text-lg font-medium text-gray-900">Claim Files</h3>
            </template>

            <template #content>
                <p>Are you sure you want to claim {{ selectedFiles.length }} file(s)?
                    Claimed files will be assigned to you for processing.</p>
            </template>

            <template #footer>
                <button @click="showClaimModal = false"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancel
                </button>
                <button @click="claimSelectedFiles"
                    class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="{ 'opacity-50 cursor-not-allowed': claimForm.processing }" :disabled="claimForm.processing">
                    {{ claimForm.processing ? 'Processing...' : 'Confirm Claim' }}
                </button>
            </template>
        </modal>
    </div>
</template>
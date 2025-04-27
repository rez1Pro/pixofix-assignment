<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

// Import our component modules
import FileStructure from '@/Components/Orders/FileStructure.vue';
import FileUploader from '@/Components/Orders/FileUploader.vue';
import FolderModal from '@/Components/Orders/FolderModal.vue';
import ProgressBar from '@/Components/Orders/ProgressBar.vue';

// Type definitions
interface WebFile extends File {
    // Extended from browser's File interface
}

interface FileItem {
    id: number;
    name: string;
    path: string;
    status: 'pending' | 'approved' | 'rejected' | 'in_progress';
    created_at: string;
    assignedTo?: {
        id: number;
        name: string;
        avatar?: string;
    } | null;
}

interface Subfolder {
    id: number;
    name: string;
    isOpen?: boolean;
    files: FileItem[];
}

interface Folder {
    id: number;
    name: string;
    isOpen?: boolean;
    subfolders: Subfolder[];
    files: FileItem[];
}

// The type expected by FileStructure component
interface FolderStructureItem {
    isOpen: boolean;
    files: {
        id: number;
        name: string;
        status: string;
    }[];
    subfolders: Record<string, {
        isOpen: boolean;
        files: {
            id: number;
            name: string;
            status: string;
        }[];
    }>;
}

interface Stats {
    total_files: number;
    approved_files: number;
    pending_files: number;
    rejected_files: number;
}

interface Order {
    id: number;
    name: string;
    status: string;
    is_approved: boolean;
    order_number: string;
    customer_name: string;
    deadline: string;
    folders: Folder[];
    stats: Stats;
}

// Define props
const props = defineProps<{
    order: Order;
    fileStructure: Record<string, FolderStructureItem>;
    userPermissions: {
        canEditOrder: boolean;
        canDeleteOrder: boolean;
        canApproveOrder: boolean;
        canCompleteOrder: boolean;
        canUploadFiles: boolean;
    };
}>();

// Stats tracking - use reactive object instead of ref
const stats = reactive<Stats>(props.order.stats);
const isRefreshing = ref(false);

// UI state
const newFolderName = ref('');
const newSubfolderName = ref('');
const selectedFolderForSubfolder = ref<string | null>(null);
const showNewFolderModal = ref(false);
const showSubfolderModal = ref(false);
const showFileUploader = ref(false);
const uploadTargetFolder = ref<string>('');
const uploadTargetSubfolder = ref<string | null>(null);

// Computed properties
const completionPercentage = computed(() => {
    if (stats.total_files === 0) return 0;
    return Math.round((stats.approved_files / stats.total_files) * 100);
});

// Folder navigation methods
const toggleFolder = (folderName: string) => {
    const folderId = props.order.folders.find(f => f.name === folderName)?.id;
    if (folderId) {
        router.put(`/folders/${folderId}/toggle-open`);
    }
};

const toggleSubfolder = (folderName: string, subfolderName: string) => {
    const folder = props.order.folders.find(f => f.name === folderName);
    const subfolderId = folder?.subfolders.find(sf => sf.name === subfolderName)?.id;

    if (subfolderId) {
        router.put(`/subfolders/${subfolderId}/toggle-open`);
    }
};

// Folder management methods
const openNewFolderModal = () => {
    showNewFolderModal.value = true;
    newFolderName.value = '';
};

const closeNewFolderModal = () => {
    showNewFolderModal.value = false;
    newFolderName.value = '';
};

const createNewFolder = () => {
    if (newFolderName.value.trim()) {
        router.post(`/orders/${props.order.id}/folders`, {
            name: newFolderName.value.trim()
        }, {
            onSuccess: () => {
                closeNewFolderModal();
            }
        });
    }
};

const openSubfolderModal = (folderName: string) => {
    showSubfolderModal.value = true;
    selectedFolderForSubfolder.value = folderName;
    newSubfolderName.value = '';
};

const closeSubfolderModal = () => {
    showSubfolderModal.value = false;
    selectedFolderForSubfolder.value = null;
    newSubfolderName.value = '';
};

const createNewSubfolder = () => {
    if (newSubfolderName.value.trim() && selectedFolderForSubfolder.value) {
        const folder = props.order.folders.find(f => f.name === selectedFolderForSubfolder.value);

        if (folder) {
            router.post(`/folders/${folder.id}/subfolders`, {
                name: newSubfolderName.value.trim()
            }, {
                onSuccess: () => {
                    closeSubfolderModal();
                }
            });
        }
    }
};

// File management methods
const openFileUploader = (folderPath: string) => {
    // The method only receives the folderPath parameter (can be 'FolderName' or 'FolderName/SubfolderName')
    console.log('Opening uploader for path:', folderPath);

    // Determine if this contains a subfolder path (e.g., "Folder/Subfolder")
    if (folderPath.includes('/')) {
        const [parentFolder, subFolder] = folderPath.split('/');
        uploadTargetFolder.value = parentFolder;
        uploadTargetSubfolder.value = subFolder;
    } else {
        uploadTargetFolder.value = folderPath;
        uploadTargetSubfolder.value = null;
    }

    showFileUploader.value = true;
};

const closeFileUploader = () => {
    showFileUploader.value = false;
    uploadTargetFolder.value = '';
    uploadTargetSubfolder.value = null;
};

// Handle file upload to ensure proper updating of both data structures
const handleFileUpload = (files: WebFile[], targetFolder: string, targetSubfolder: string | null = null) => {
    if (!files.length) return;

    const formData = new FormData();
    Array.from(files).forEach(file => {
        formData.append('files[]', file);
    });

    const folder = props.order.folders.find(f => f.name === targetFolder);
    if (!folder) return;

    if (targetSubfolder) {
        const subfolder = folder.subfolders.find(sf => sf.name === targetSubfolder);
        if (subfolder) {
            router.post(`/subfolders/${subfolder.id}/files`, formData);
        }
    } else {
        router.post(`/folders/${folder.id}/files`, formData);
    }

    closeFileUploader();
};

// Data refresh method
const refreshData = () => {
    isRefreshing.value = true;
    router.visit(`/orders/${props.order.id}/refresh-stats`, {
        only: ['stats'],
        onSuccess: () => {
            isRefreshing.value = false;
        },
        preserveScroll: true,
    });
};

// Order management methods
const markOrderCompleted = () => {
    router.post(`/orders/${props.order.id}/mark-completed`);
};

const approveOrder = () => {
    router.post(`/orders/${props.order.id}/approve`);
};

// Handle file assignment
const handleAssignFiles = (folderName: string, fileIds: number[]) => {
    if (fileIds.length === 0) return;

    router.post(`/orders/${props.order.id}/files/assign-to-self`, {
        file_ids: fileIds
    });

    // Download the assigned files automatically
    downloadFiles(fileIds);
};

const handleAssignSubfolderFiles = (folderName: string, subfolderName: string, fileIds: number[]) => {
    if (fileIds.length === 0) return;

    router.post(`/orders/${props.order.id}/files/assign-to-self`, {
        file_ids: fileIds
    });

    // Download the assigned files automatically
    downloadFiles(fileIds);
};

// Handle file download
const handleDownloadFile = (folderName: string, fileId: number) => {
    downloadFiles([fileId]);
};

// Handle file download from subfolder
const handleDownloadSubfolderFile = (folderName: string, subfolderName: string, fileId: number) => {
    downloadFiles([fileId]);
};

// Download files
const downloadFiles = (fileIds: number[]) => {
    if (fileIds.length === 0) return;

    router.post(`/orders/${props.order.id}/files/download`, {
        file_ids: fileIds
    }, {
        preserveScroll: true
    });
};

// Delete order
const deleteOrder = () => {
    if (confirm(`Are you sure you want to delete order ${props.order.name}? This action cannot be undone.`)) {
        router.delete(`/orders/${props.order.id}`);
    }
};
</script>

<template>

    <Head title="Order Details" />

    <AuthenticatedLayout header="Order Details" description="View and manage order information">
        <!-- Header section with actions -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Order Details
                </h2>
                <div class="flex space-x-2">
                    <Link v-if="userPermissions.canEditOrder" :href="`/orders/${props.order.id}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit Order
                    </Link>
                    <button v-if="userPermissions.canApproveOrder && props.order.status === 'completed'"
                        @click="approveOrder"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Approve Order
                    </button>
                    <button v-if="userPermissions.canCompleteOrder && props.order.status !== 'completed'"
                        @click="markOrderCompleted"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Mark as Completed
                    </button>
                    <button v-if="userPermissions.canDeleteOrder" @click="deleteOrder"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Delete Order
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Order information -->
                        <div class="md:flex md:justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-bold mb-4">{{ props.order.name }}</h3>
                                <div class="flex items-center mb-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Order Number:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ props.order.order_number }}
                                    </span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Customer:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ props.order.customer_name || 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Status:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                        'bg-yellow-100 text-yellow-800': props.order.status === 'pending',
                                        'bg-blue-100 text-blue-800': props.order.status === 'in_progress',
                                        'bg-green-100 text-green-800': props.order.status === 'completed',
                                        'bg-purple-100 text-purple-800': props.order.status === 'approved'
                                    }">
                                        {{ props.order.status }}
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Deadline:</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ props.order.deadline ?
                                            new Date(props.order.deadline).toLocaleDateString() :
                                            'No deadline set'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Progress Section -->
                        <ProgressBar :percentage="completionPercentage" :total="stats.total_files"
                            :completed="stats.approved_files" :is-refreshing="isRefreshing" @refresh="refreshData" />

                        <!-- File Structure Section -->
                        <FileStructure :folders="fileStructure" @toggle-folder="toggleFolder"
                            @toggle-subfolder="toggleSubfolder" @add-root-folder="openNewFolderModal"
                            @add-subfolder="openSubfolderModal" @upload-files="openFileUploader"
                            @assign-files="handleAssignFiles" @assign-subfolder-files="handleAssignSubfolderFiles"
                            @download-file="handleDownloadFile"
                            @download-subfolder-file="handleDownloadSubfolderFile" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Root Folder Modal -->
        <FolderModal title="Create New Folder" input-label="Folder Name" placeholder="Enter folder name"
            v-model="newFolderName" :show-modal="showNewFolderModal" @close="closeNewFolderModal"
            @submit="createNewFolder" />

        <!-- Subfolder Modal -->
        <FolderModal title="Create New Subfolder" input-label="Subfolder Name" placeholder="Enter subfolder name"
            v-model="newSubfolderName" :parent-folder="selectedFolderForSubfolder" :show-modal="showSubfolderModal"
            @close="closeSubfolderModal" @submit="createNewSubfolder" />

        <!-- File Uploader -->
        <FileUploader :target-folder="uploadTargetFolder" :target-subfolder="uploadTargetSubfolder"
            :show-modal="showFileUploader" @close="closeFileUploader" @upload="handleFileUpload" />
    </AuthenticatedLayout>
</template>
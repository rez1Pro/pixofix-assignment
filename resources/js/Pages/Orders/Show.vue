<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, reactive, ref } from 'vue';

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
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
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
    folders: Folder[];
    stats: Stats;
    is_approved: boolean;
    order_number: string;
    customer_name: string;
    deadline: string;
}

// Mock data for demonstration
const order = ref<Order>({
    id: 123,
    name: 'Test Order #123',
    status: 'Processing',
    order_number: 'ORD-2023-001',
    customer_name: 'John Doe',
    deadline: '2023-12-31',
    is_approved: false,
    folders: [
        {
            id: 1,
            name: 'Original Images',
            isOpen: true,
            subfolders: [
                {
                    id: 11,
                    name: 'Batch 1',
                    isOpen: true,
                    files: [
                        {
                            id: 111,
                            name: 'image1.jpg',
                            path: '/storage/images/image1.jpg',
                            status: 'approved',
                            created_at: '2023-11-01'
                        },
                        {
                            id: 112,
                            name: 'image2.jpg',
                            path: '/storage/images/image2.jpg',
                            status: 'pending',
                            created_at: '2023-11-01'
                        }
                    ]
                }
            ],
            files: [
                {
                    id: 101,
                    name: 'main.jpg',
                    path: '/storage/images/main.jpg',
                    status: 'pending',
                    created_at: '2023-11-01'
                }
            ]
        },
        {
            id: 2,
            name: 'Edited Images',
            isOpen: false,
            subfolders: [],
            files: [
                {
                    id: 201,
                    name: 'edited-main.jpg',
                    path: '/storage/images/edited-main.jpg',
                    status: 'pending',
                    created_at: '2023-11-02'
                }
            ]
        }
    ],
    stats: {
        total_files: 35,
        approved_files: 12,
        pending_files: 20,
        rejected_files: 3
    }
});

// Stats tracking - use reactive object instead of ref
const stats = reactive<Stats>(order.value.stats);
const isRefreshing = ref(false);

// File structure data with proper typing for the component
const fileStructure = ref<Record<string, FolderStructureItem>>({});

// Convert folders array to the expected Record format
const updateFileStructure = () => {
    const result: Record<string, FolderStructureItem> = {};

    order.value.folders.forEach(folder => {
        // Convert subfolders array to Record structure keyed by subfolder name
        const subfolders: Record<string, { isOpen: boolean, files: any[] }> = {};

        folder.subfolders.forEach(subfolder => {
            subfolders[subfolder.name] = {
                isOpen: subfolder.isOpen || false,
                files: subfolder.files.map(file => ({
                    id: file.id,
                    name: file.name,
                    status: file.status
                }))
            };
        });

        // Add the folder to the result Record with folder name as key
        result[folder.name] = {
            isOpen: folder.isOpen || false,
            files: folder.files.map(file => ({
                id: file.id,
                name: file.name,
                status: file.status
            })),
            subfolders: subfolders
        };
    });

    fileStructure.value = result;
};

// Initialize file structure
updateFileStructure();

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
    // Update the component's data structure
    if (fileStructure.value[folderName]) {
        fileStructure.value[folderName].isOpen = !fileStructure.value[folderName].isOpen;

        // Also update the original data structure
        const folder = order.value.folders.find(f => f.name === folderName);
        if (folder) {
            folder.isOpen = fileStructure.value[folderName].isOpen;
        }
    }
};

const toggleSubfolder = (folderName: string, subfolderName: string) => {
    // Update the component's data structure
    if (fileStructure.value[folderName]?.subfolders[subfolderName]) {
        fileStructure.value[folderName].subfolders[subfolderName].isOpen =
            !fileStructure.value[folderName].subfolders[subfolderName].isOpen;

        // Also update the original data structure
        const folder = order.value.folders.find(f => f.name === folderName);
        if (folder) {
            const subfolder = folder.subfolders.find(sf => sf.name === subfolderName);
            if (subfolder) {
                subfolder.isOpen = fileStructure.value[folderName].subfolders[subfolderName].isOpen;
            }
        }
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
        // First add to the original data structure
        const newId = Math.max(0, ...order.value.folders.map(f => f.id)) + 1;
        order.value.folders.push({
            id: newId,
            name: newFolderName.value.trim(),
            isOpen: true,
            subfolders: [],
            files: []
        });

        // Then update the file structure for the component
        updateFileStructure();
        closeNewFolderModal();
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
        // First find and update in the original data structure
        const folder = order.value.folders.find(f => f.name === selectedFolderForSubfolder.value);
        if (folder) {
            const newId = Math.max(0, ...folder.subfolders.map(sf => sf.id)) + 1;
            folder.subfolders.push({
                id: newId,
                name: newSubfolderName.value.trim(),
                isOpen: true,
                files: []
            });

            // Then update the file structure for the component
            updateFileStructure();
            closeSubfolderModal();
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
    console.log('Uploading files to:', targetFolder, targetSubfolder);

    // Find the target folder in original structure
    const folder = order.value.folders.find(f => f.name === targetFolder);
    if (!folder) {
        console.error('Folder not found:', targetFolder);
        return;
    }

    // Force the folder to be open
    folder.isOpen = true;

    if (targetSubfolder) {
        // Upload to subfolder
        const subfolder = folder.subfolders.find(sf => sf.name === targetSubfolder);
        if (subfolder) {
            // Force the subfolder to be open
            subfolder.isOpen = true;

            console.log('Adding files to subfolder:', subfolder.name);

            // Create new files
            const newFiles: FileItem[] = Array.from(files).map((file) => ({
                id: Math.floor(Math.random() * 10000), // Generate a random ID for demo
                name: file.name,
                path: URL.createObjectURL(file), // Use URL.createObjectURL for local preview
                status: 'pending',
                created_at: new Date().toISOString().split('T')[0]
            }));

            subfolder.files = [...subfolder.files, ...newFiles];

            // Update stats
            stats.total_files += newFiles.length;
            stats.pending_files += newFiles.length;
        } else {
            console.error('Subfolder not found:', targetSubfolder);
        }
    } else {
        console.log('Adding files to main folder:', folder.name);

        // Upload to root folder
        const newFiles: FileItem[] = Array.from(files).map((file) => ({
            id: Math.floor(Math.random() * 10000), // Generate a random ID for demo
            name: file.name,
            path: URL.createObjectURL(file), // Use URL.createObjectURL for local preview
            status: 'pending',
            created_at: new Date().toISOString().split('T')[0]
        }));

        folder.files = [...folder.files, ...newFiles];

        // Update stats
        stats.total_files += newFiles.length;
        stats.pending_files += newFiles.length;
    }

    closeFileUploader();

    // Update the file structure for the component
    updateFileStructure();

    // Force a refresh of the UI if needed
    nextTick(() => {
        // This ensures the DOM updates after the data changes
        console.log('UI updated after file upload');
    });
};

// Data refresh method
const refreshData = () => {
    isRefreshing.value = true;
    // Simulate data refresh
    setTimeout(() => {
        isRefreshing.value = false;
    }, 1000);
};

// Order management methods
const markOrderCompleted = () => {
    order.value.status = 'completed';
    // In a real application, you would send a request to update the order status
};

const approveOrder = () => {
    order.value.is_approved = true;
    // In a real application, you would send a request to approve the order
};

// Refresh stats from the actual data structure
const refreshStats = () => {
    // In a real application, you would fetch fresh stats from the server
    // This is a mock implementation that recalculates based on the current state
    let approved = 0;
    let pending = 0;
    let rejected = 0;
    let total = 0;

    order.value.folders.forEach(folder => {
        // Count files in the root of the folder
        folder.files.forEach(file => {
            total++;
            if (file.status === 'approved') approved++;
            else if (file.status === 'pending') pending++;
            else if (file.status === 'rejected') rejected++;
        });

        // Count files in subfolders
        folder.subfolders.forEach(subfolder => {
            subfolder.files.forEach(file => {
                total++;
                if (file.status === 'approved') approved++;
                else if (file.status === 'pending') pending++;
                else if (file.status === 'rejected') rejected++;
            });
        });
    });

    stats.total_files = total;
    stats.approved_files = approved;
    stats.pending_files = pending;
    stats.rejected_files = rejected;
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
                    <Link :href="`/orders/${order.id}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit Order
                    </Link>
                    <button v-if="order.status === 'completed'" @click="approveOrder"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Approve Order
                    </button>
                    <button v-else-if="order.status === 'in_progress'" @click="markOrderCompleted"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Mark as Completed
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Order Progress Section -->
                        <ProgressBar :percentage="completionPercentage" :total="stats.total_files"
                            :completed="stats.approved_files" :is-refreshing="isRefreshing" @refresh="refreshStats" />

                        <!-- File Structure Section -->
                        <FileStructure :folders="fileStructure" @toggle-folder="toggleFolder"
                            @toggle-subfolder="toggleSubfolder" @add-root-folder="openNewFolderModal"
                            @add-subfolder="openSubfolderModal" @upload-files="openFileUploader" />
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
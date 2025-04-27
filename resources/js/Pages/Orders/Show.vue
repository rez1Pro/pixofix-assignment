<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
                            created_at: '2023-11-01',
                            assignedTo: {
                                id: 2,
                                name: 'Jane Designer',
                                avatar: 'https://i.pravatar.cc/150?img=5'
                            }
                        },
                        {
                            id: 112,
                            name: 'image2.jpg',
                            path: '/storage/images/image2.jpg',
                            status: 'in_progress',
                            created_at: '2023-11-01',
                            assignedTo: {
                                id: 1,
                                name: 'Current User',
                                avatar: 'https://i.pravatar.cc/150?img=11'
                            }
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
                    status: 'in_progress',
                    created_at: '2023-11-02',
                    assignedTo: {
                        id: 3,
                        name: 'Alex Editor',
                        avatar: 'https://i.pravatar.cc/150?img=68'
                    }
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

// Add a currentUser object to simulate the logged-in user
const currentUser = {
    id: 1,
    name: 'Current User',
    email: 'user@example.com',
    role: 'Designer',
    avatar: 'https://i.pravatar.cc/150?img=11' // Add a sample avatar URL
};

// Handle file assignment
const handleAssignFiles = (folderName: string, fileIds: number[]) => {
    console.log(`Assigning ${fileIds.length} files from folder "${folderName}" to current user`);

    // In a real application, you would make an API call to assign the files to the current user
    assignFilesToCurrentUser(folderName, null, fileIds);

    // Download the assigned files automatically
    downloadFiles(folderName, null, fileIds);
};

const handleAssignSubfolderFiles = (folderName: string, subfolderName: string, fileIds: number[]) => {
    console.log(`Assigning ${fileIds.length} files from subfolder "${subfolderName}" in folder "${folderName}" to current user`);

    // In a real application, you would make an API call to assign the files to the current user
    assignFilesToCurrentUser(folderName, subfolderName, fileIds);

    // Download the assigned files automatically
    downloadFiles(folderName, subfolderName, fileIds);
};

// Simulate assigning files to the current user
const assignFilesToCurrentUser = (folderName: string, subfolderName: string | null, fileIds: number[]) => {
    // Find the correct folder
    const folder = order.value.folders.find(f => f.name === folderName);
    if (!folder) return;

    if (subfolderName) {
        // Find the correct subfolder
        const subfolder = folder.subfolders.find(sf => sf.name === subfolderName);
        if (!subfolder) return;

        // Update the files' status and assign to current user
        fileIds.forEach(fileId => {
            const file = subfolder.files.find(f => f.id === fileId);
            if (file) {
                file.status = 'in_progress'; // Change status to indicate assignment
                file.assignedTo = {
                    id: currentUser.id,
                    name: currentUser.name,
                    avatar: currentUser.avatar
                };
            }
        });
    } else {
        // Update files in the root folder
        fileIds.forEach(fileId => {
            const file = folder.files.find(f => f.id === fileId);
            if (file) {
                file.status = 'in_progress'; // Change status to indicate assignment
                file.assignedTo = {
                    id: currentUser.id,
                    name: currentUser.name,
                    avatar: currentUser.avatar
                };
            }
        });
    }

    // Update the file structure data
    updateFileStructure();

    // Show a success notification (in a real app)
    alert(`${fileIds.length} files assigned to you successfully!`);
};

// Handle file download from root folder
const handleDownloadFile = (folderName: string, fileId: number) => {
    console.log(`Downloading file ${fileId} from folder "${folderName}"`);
    downloadFiles(folderName, null, [fileId]);
};

// Handle file download from subfolder
const handleDownloadSubfolderFile = (folderName: string, subfolderName: string, fileId: number) => {
    console.log(`Downloading file ${fileId} from subfolder "${subfolderName}" in folder "${folderName}"`);
    downloadFiles(folderName, subfolderName, [fileId]);
};

// Simulate downloading files
const downloadFiles = (folderName: string, subfolderName: string | null, fileIds: number[]) => {
    // Find the correct folder
    const folder = order.value.folders.find(f => f.name === folderName);
    if (!folder) return;

    let filesToDownload = [];

    if (subfolderName) {
        // Find the correct subfolder
        const subfolder = folder.subfolders.find(sf => sf.name === subfolderName);
        if (!subfolder) return;

        // Get files from the subfolder
        filesToDownload = subfolder.files.filter(file => fileIds.includes(file.id));
    } else {
        // Get files from the root folder
        filesToDownload = folder.files.filter(file => fileIds.includes(file.id));
    }

    // In a real application, you would initiate downloads for these files
    // For demo purposes, we'll just log the files being downloaded
    console.log('Downloading files:', filesToDownload);

    // For demo: Create a simple download function
    filesToDownload.forEach(file => {
        // In a real app, you would use file.path to download
        if (file.path) {
            // For browser-based blob URLs, we can open them directly
            if (file.path.startsWith('blob:')) {
                window.open(file.path, '_blank');
            } else {
                // For server paths, you would typically hit an endpoint
                // Something like: window.location.href = `/api/files/${file.id}/download`;
                alert(`Downloading file: ${file.name}`);
            }
        } else {
            alert(`Cannot download file: ${file.name} - No path available`);
        }
    });
};

// Add a delete order method
const deleteOrder = () => {
    if (confirm(`Are you sure you want to delete order ${order.value.name}? This action cannot be undone.`)) {
        router.delete(`/orders/${order.value.id}`, {
            onSuccess: () => {
                // Redirect happens automatically
            },
        });
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
                    <button @click="deleteOrder"
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
                        <!-- Order Progress Section -->
                        <ProgressBar :percentage="completionPercentage" :total="stats.total_files"
                            :completed="stats.approved_files" :is-refreshing="isRefreshing" @refresh="refreshStats" />

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

        <!-- New display components to show customer information -->
        <div class="md:flex md:justify-between items-start mb-6">
            <div>
                <div class="flex items-center mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Order Number:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ order.order_number }}</span>
                </div>
                <div class="flex items-center mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Customer:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ order.customer_name || 'N/A'
                        }}</span>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Deadline:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ order.deadline ? new Date(order.deadline).toLocaleDateString() : 'No deadline set' }}
                    </span>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
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
        </div>
    </AuthenticatedLayout>
</template>
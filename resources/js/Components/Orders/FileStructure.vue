<script setup lang="ts">
import FolderItem from './FolderItem.vue';

interface File {
    id: number;
    name: string;
    status: string;
    assignedTo?: {
        id: number;
        name: string;
    } | null;
    path?: string;
}

interface Subfolder {
    isOpen: boolean;
    files: File[];
}

interface Folder {
    isOpen: boolean;
    files: File[];
    subfolders: Record<string, Subfolder>;
}

const props = defineProps<{
    folders: Record<string, Folder>;
}>();

const emit = defineEmits<{
    toggleFolder: [folderName: string];
    toggleSubfolder: [folderName: string, subfolderName: string];
    addRootFolder: [];
    addSubfolder: [folderName: string];
    uploadFiles: [folderName: string];
    assignFiles: [folderName: string, fileIds: number[]];
    assignSubfolderFiles: [folderName: string, subfolderName: string, fileIds: number[]];
    downloadFile: [folderName: string, fileId: number];
    downloadSubfolderFile: [folderName: string, subfolderName: string, fileId: number];
}>();

const handleToggleFolder = (folderName: string) => {
    emit('toggleFolder', folderName);
};

const handleToggleSubfolder = (folderName: string, subfolderName: string) => {
    emit('toggleSubfolder', folderName, subfolderName);
};

const handleAddSubfolder = (folderName: string) => {
    emit('addSubfolder', folderName);
};

const handleUploadFiles = (folderName: string) => {
    emit('uploadFiles', folderName);
};

const handleUploadToSubfolder = (folderPath: string) => {
    // For consistency, we'll use the same event but pass the full path
    emit('uploadFiles', folderPath);
};

const handleAssignFiles = (folderName: string, fileIds: number[]) => {
    emit('assignFiles', folderName, fileIds);
};

const handleAssignSubfolderFiles = (folderName: string, subfolderName: string, fileIds: number[]) => {
    emit('assignSubfolderFiles', folderName, subfolderName, fileIds);
};

const handleDownloadFile = (folderName: string, fileId: number) => {
    emit('downloadFile', folderName, fileId);
};

const handleDownloadSubfolderFile = (folderName: string, subfolderName: string, fileId: number) => {
    emit('downloadSubfolderFile', folderName, subfolderName, fileId);
};

const addRootFolder = () => {
    emit('addRootFolder');
};
</script>

<template>
    <section class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">File Structure</h3>

        <!-- Empty state -->
        <div v-if="!folders || Object.keys(folders).length === 0" class="text-center py-8">
            <p class="text-gray-500">No files have been uploaded yet.</p>
        </div>

        <!-- File tree -->
        <div v-else class="border rounded-lg">
            <!-- Root folders -->
            <FolderItem v-for="(folder, folderName) in folders" :key="folderName" :name="folderName" :folder="folder"
                @toggle-folder="handleToggleFolder" @toggle-subfolder="handleToggleSubfolder"
                @add-subfolder="handleAddSubfolder" @upload-files="handleUploadFiles"
                @upload-to-subfolder="handleUploadToSubfolder" @assign-files="handleAssignFiles"
                @assign-subfolder-files="handleAssignSubfolderFiles" @download-file="handleDownloadFile"
                @download-subfolder-file="handleDownloadSubfolderFile" />
        </div>

        <!-- Add root folder button -->
        <div class="mt-4">
            <button @click="addRootFolder"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                        clip-rule="evenodd" />
                </svg>
                Add Root Folder
            </button>
        </div>
    </section>
</template>
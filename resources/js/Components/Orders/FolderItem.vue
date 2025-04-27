<script setup lang="ts">
import FileItem from './FileItem.vue';
import SubfolderItem from './SubfolderItem.vue';

interface File {
    id: number;
    name: string;
    status: string;
}

interface Subfolder {
    isOpen: boolean;
    files: File[];
}

interface FolderProps {
    name: string;
    folder: {
        isOpen: boolean;
        files: File[];
        subfolders: Record<string, Subfolder>;
    };
}

const props = defineProps<FolderProps>();

const emit = defineEmits<{
    toggleFolder: [folderName: string];
    toggleSubfolder: [folderName: string, subfolderName: string];
    addSubfolder: [folderName: string];
    uploadFiles: [folderName: string];
    uploadToSubfolder: [folderPath: string];
}>();

const toggleFolder = () => {
    emit('toggleFolder', props.name);
};

const handleSubfolderToggle = (parentFolder: string, subfolderName: string) => {
    emit('toggleSubfolder', parentFolder, subfolderName);
};

const addSubfolder = () => {
    emit('addSubfolder', props.name);
};

const uploadFiles = () => {
    emit('uploadFiles', props.name);
};

const handleUploadToSubfolder = (parentFolder: string, subfolderName: string) => {
    const folderPath = `${parentFolder}/${subfolderName}`;
    emit('uploadToSubfolder', folderPath);
};
</script>

<template>
    <div class="folder-item border-b last:border-b-0">
        <!-- Folder header -->
        <div @click="toggleFolder" class="flex items-center p-3 cursor-pointer hover:bg-gray-50">
            <svg :class="{ 'transform rotate-90': folder.isOpen }"
                class="w-4 h-4 mr-2 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
            </svg>
            <svg class="w-5 h-5 mr-2 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ name }}</span>
            <span class="ml-2 text-sm text-gray-500">({{ folder.files.length }} files)</span>
        </div>

        <!-- Folder contents (shown when expanded) -->
        <div v-if="folder.isOpen" class="pl-10 pr-3 pb-3">
            <!-- Subfolders -->
            <SubfolderItem v-for="(subfolder, subfolderName) in folder.subfolders" :key="subfolderName"
                :name="subfolderName" :folder="subfolder" :parent-folder="name" @toggle="handleSubfolderToggle"
                @upload-to-subfolder="handleUploadToSubfolder" />

            <!-- Root folder files -->
            <FileItem v-for="file in folder.files" :key="file.id" :file="file" class="mt-2" />

            <!-- Folder actions -->
            <div class="mt-4">
                <button @click.stop="addSubfolder"
                    class="inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Add Subfolder
                </button>
                <button @click.stop="uploadFiles"
                    class="ml-2 inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Upload Files
                </button>
            </div>
        </div>
    </div>
</template>
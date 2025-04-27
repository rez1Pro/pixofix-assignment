<script setup lang="ts">
import { ref } from 'vue';
import FileItem from './FileItem.vue';

interface File {
    id: number;
    name: string;
    status: string;
    assignedTo?: {
        id: number;
        name: string;
        avatar?: string;
    } | null;
    path?: string;
}

interface SubfolderProps {
    name: string;
    folder: {
        isOpen: boolean;
        files: File[];
    };
    parentFolder: string;
}

const props = defineProps<SubfolderProps>();

const emit = defineEmits<{
    toggle: [folderName: string, subfolderName: string];
    uploadToSubfolder: [parentFolder: string, subfolderName: string];
    assignFiles: [parentFolder: string, subfolderName: string, fileIds: number[]];
    downloadFile: [parentFolder: string, subfolderName: string, fileId: number];
}>();

const toggleSubfolder = () => {
    emit('toggle', props.parentFolder, props.name);
};

const uploadFiles = () => {
    emit('uploadToSubfolder', props.parentFolder, props.name);
};

// File selection
const selectedFileIds = ref<Set<number>>(new Set());
const selectionMode = ref(false);

const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value;
    if (!selectionMode.value) {
        selectedFileIds.value.clear();
    }
};

const handleFileSelection = (fileId: number, selected: boolean) => {
    if (selected) {
        selectedFileIds.value.add(fileId);
    } else {
        selectedFileIds.value.delete(fileId);
    }
};

const assignSelectedFilesToMe = () => {
    if (selectedFileIds.value.size > 0) {
        emit('assignFiles', props.parentFolder, props.name, Array.from(selectedFileIds.value));
        // Reset selection after assigning
        selectedFileIds.value.clear();
        selectionMode.value = false;
    }
};

const handleDownloadFile = (fileId: number) => {
    emit('downloadFile', props.parentFolder, props.name, fileId);
};
</script>

<template>
    <div class="mt-2">
        <!-- Subfolder header -->
        <div @click="toggleSubfolder" class="flex items-center p-2 cursor-pointer hover:bg-gray-50 rounded">
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

        <!-- Subfolder files (shown when expanded) -->
        <div v-if="folder.isOpen" class="pl-8 mt-2">
            <!-- Files with checkboxes when selection mode is active -->
            <FileItem v-for="file in folder.files" :key="file.id" :file="file" :selectable="selectionMode"
                :selected="selectedFileIds.has(file.id)" @select="handleFileSelection" @download="handleDownloadFile" />

            <!-- Actions toolbar -->
            <div class="mt-3 flex flex-wrap gap-2">
                <button @click.stop="uploadFiles"
                    class="inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Upload Files
                </button>

                <!-- Select button -->
                <button @click.stop="toggleSelectionMode"
                    class="inline-flex items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :class="{ 'bg-indigo-100': selectionMode }">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ selectionMode ? 'Cancel Selection' : 'Select Files' }}
                </button>

                <!-- Assign to me button - only shown when files are selected -->
                <button v-if="selectionMode && selectedFileIds.size > 0" @click.stop="assignSelectedFilesToMe"
                    class="inline-flex items-center px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                    Assign to Me ({{ selectedFileIds.size }})
                </button>
            </div>
        </div>
    </div>
</template>
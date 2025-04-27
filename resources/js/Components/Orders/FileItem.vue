<script setup lang="ts">
interface FileProps {
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

const props = defineProps<{
    file: FileProps;
    selectable?: boolean;
    selected?: boolean;
}>();

const emit = defineEmits<{
    select: [fileId: number, selected: boolean];
    download: [fileId: number];
}>();

const toggleSelect = (event: Event) => {
    const checked = (event.target as HTMLInputElement).checked;
    emit('select', props.file.id, checked);
};

const downloadFile = (event: Event) => {
    event.stopPropagation();
    emit('download', props.file.id);
};
</script>

<template>
    <div class="flex items-center p-2 hover:bg-gray-50 rounded group">
        <input v-if="selectable" type="checkbox" :checked="selected" @change="toggleSelect"
            class="mr-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
        <svg class="w-5 h-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
            fill="currentColor">
            <path fill-rule="evenodd"
                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                clip-rule="evenodd" />
        </svg>

        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <span class="text-sm font-medium">{{ file.name }}</span>

                <!-- Assignment Badge - Always visible -->
                <span v-if="file.assignedTo"
                    class="ml-2 px-2 py-0.5 text-xs bg-indigo-100 text-indigo-800 rounded-full flex items-center">
                    <template v-if="file.assignedTo.avatar">
                        <img :src="file.assignedTo.avatar" :alt="file.assignedTo.name"
                            class="w-4 h-4 rounded-full mr-1 object-cover" />
                    </template>
                    <svg v-else class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ file.assignedTo.name }}
                </span>
            </div>

            <div class="flex items-center space-x-2 mt-1">
                <span v-if="file.status === 'completed'"
                    class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full">Completed</span>
                <span v-else-if="file.status === 'in_progress'"
                    class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">In Progress</span>
                <span v-else class="px-2 py-0.5 text-xs bg-gray-100 text-gray-800 rounded-full">Pending</span>
            </div>
        </div>

        <!-- Actions (visible on hover) -->
        <div class="hidden group-hover:flex items-center space-x-2">
            <button @click="downloadFile" class="p-1 text-gray-500 hover:text-indigo-600 focus:outline-none"
                title="Download file">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</template>
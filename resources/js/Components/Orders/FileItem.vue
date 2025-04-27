<script setup lang="ts">
interface FileProps {
    id: number;
    name: string;
    status: string;
}

defineProps<{
    file: FileProps
}>();
</script>

<template>
    <div class="flex items-center p-2 hover:bg-gray-50 rounded">
        <svg class="w-5 h-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
            fill="currentColor">
            <path fill-rule="evenodd"
                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                clip-rule="evenodd" />
        </svg>
        <span>{{ file.name }}</span>
        <span v-if="file.status === 'completed'"
            class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full">Completed</span>
        <span v-else-if="file.status === 'in_progress'"
            class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">In Progress</span>
        <span v-else class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-800 rounded-full">Pending</span>
    </div>
</template>
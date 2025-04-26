<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    CheckCircleIcon,
    DocumentIcon,
    FolderIcon,
    PaperClipIcon,
    PencilSquareIcon
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/24/solid';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, computed } from 'vue';

interface FileItem {
    id: number;
    order_id: number;
    filename: string;
    original_filename: string;
    filepath: string;
    directory_path: string | null;
    file_type: string;
    file_size: number;
    is_processed: boolean;
    status: string;
    created_at: string;
    updated_at: string;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    role?: { name: string };
}

interface OrderData {
    id: number;
    order_number: string;
    name: string;
    description: string | null;
    created_by: number;
    status: string;
    completed_at: string | null;
    approved_at: string | null;
    created_at: string;
    updated_at: string;
    creator: UserData;
}

const props = defineProps({
    order: Object as PropType<OrderData>,
    filesGrouped: Object as PropType<Record<string, FileItem[]>>,
    stats: Object as PropType<{
        total: number;
        pending: number;
        claimed: number;
        completed: number;
    }>,
});

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const formatFileSize = (size: number): string => {
    if (size < 1024) {
        return size + ' B';
    } else if (size < 1024 * 1024) {
        return (size / 1024).toFixed(2) + ' KB';
    } else {
        return (size / (1024 * 1024)).toFixed(2) + ' MB';
    }
};

const getStatusClass = (status: string): string => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'claimed':
        case 'processing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const getStatusText = (status: string): string => {
    switch (status) {
        case 'pending':
            return 'Pending';
        case 'claimed':
            return 'Claimed';
        case 'processing':
            return 'Processing';
        case 'completed':
            return 'Completed';
        default:
            return status;
    }
};

const getOrderStatusClass = (status: string): string => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'approved':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const getOrderStatusText = (status: string): string => {
    switch (status) {
        case 'pending':
            return 'Pending';
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
        case 'approved':
            return 'Approved';
        default:
            return status;
    }
};

const canMarkAsCompleted = computed(() => {
    return props.order.status !== 'completed' && props.order.status !== 'approved' && props.stats.total > 0 && props.stats.completed === props.stats.total;
});

const canApprove = computed(() => {
    return props.order.status === 'completed';
});

const markAsCompleted = () => {
    router.post(`/orders/${props.order.id}/mark-completed`, {}, {
        preserveScroll: true,
    });
};

const approveOrder = () => {
    router.post(`/orders/${props.order.id}/approve`, {}, {
        preserveScroll: true,
    });
};

const progressPercentage = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.completed / props.stats.total) * 100);
});
</script>

<template>
    <AuthenticatedLayout :header="order.name" :description="order.order_number">

        <Head :title="order.name" />

        <div class="space-y-6">
            <!-- Order Info Card -->
            <div
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-12 w-12 flex items-center justify-center rounded-md bg-indigo-100 dark:bg-indigo-900">
                                    <FolderIcon class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ order.name }}</h2>
                                    <div class="flex items-center mt-1">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ order.order_number }}
                                        </p>
                                        <span
                                            class="mx-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getOrderStatusClass(order.status)">
                                            {{ getOrderStatusText(order.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-300" v-if="order.description">
                                {{ order.description }}
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                            <Link :href="`/orders/${order.id}/edit`"
                                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-500 dark:hover:bg-gray-600">
                            <PencilSquareIcon class="h-5 w-5 mr-2 text-gray-400 dark:text-gray-400" />
                            Edit
                            </Link>
                            <button v-if="canMarkAsCompleted" @click="markAsCompleted"
                                class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 dark:bg-green-500 dark:hover:bg-green-400">
                                <CheckIcon class="h-5 w-5 mr-2" />
                                Mark as Completed
                            </button>
                            <button v-if="canApprove" @click="approveOrder"
                                class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 dark:bg-purple-500 dark:hover:bg-purple-400">
                                <CheckCircleIcon class="h-5 w-5 mr-2" />
                                Approve
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created by</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                    {{ order.creator.name }}
                                    <span v-if="order.creator.role" class="text-gray-500 dark:text-gray-400">
                                        ({{ order.creator.role.name }})
                                    </span>
                                </dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created at</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                    {{ formatDate(order.created_at) }}
                                </dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4" v-if="order.completed_at">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed at</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                    {{ formatDate(order.completed_at) }}
                                </dd>
                            </div>
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4" v-if="order.approved_at">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved at</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                    {{ formatDate(order.approved_at) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Progress</h3>
                    <div class="mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ progressPercentage }}% Complete
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ stats.completed }} / {{ stats.total }} files
                            </span>
                        </div>
                        <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 dark:bg-indigo-400"
                                :style="{ width: `${progressPercentage}%` }"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div
                                class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Files
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                    {{ stats.total }}
                                </dd>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending</dt>
                                <dd class="mt-1 text-3xl font-semibold text-yellow-600 dark:text-yellow-400">
                                    {{ stats.pending }}
                                </dd>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">In Progress
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                                    {{ stats.claimed }}
                                </dd>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Completed</dt>
                                <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                                    {{ stats.completed }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Files</h3>
                        <Link :href="`/orders/${order.id}/files/create`"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                        Upload Files
                        </Link>
                    </div>

                    <!-- Display files grouped by directory -->
                    <div v-if="Object.keys(filesGrouped).length > 0" class="mt-4 space-y-6">
                        <div v-for="(files, directory) in filesGrouped" :key="directory" class="space-y-2">
                            <div class="flex items-center">
                                <FolderIcon class="h-5 w-5 text-gray-400 mr-2" />
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ directory === 'root' ? 'Root Directory' : directory }}
                                </h4>
                            </div>

                            <ul class="mt-1 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <li v-for="file in files" :key="file.id"
                                    class="col-span-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 shadow-sm overflow-hidden">
                                    <div class="flex items-center p-3 border-b border-gray-200 dark:border-gray-600">
                                        <DocumentIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        <div class="flex-1 truncate">
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                    :class="getStatusClass(file.status)">
                                                    {{ getStatusText(file.status) }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white truncate">
                                                {{ file.original_filename || file.filename }}
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatFileSize(file.file_size) }}
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 flex justify-between items-center">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatDate(file.created_at) }}
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link :href="`/files/${file.id}/preview`"
                                                class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Preview
                                            </Link>
                                            <Link :href="`/files/${file.id}/download`"
                                                class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Download
                                            </Link>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-else
                        class="mt-4 px-6 py-10 text-center text-gray-500 dark:text-gray-400 border border-dashed border-gray-300 dark:border-gray-600 rounded-md">
                        <div class="flex flex-col items-center justify-center">
                            <DocumentIcon class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No files found</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Get started by uploading files to this order.
                            </p>
                            <Link :href="`/orders/${order.id}/files/create`"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                            <PaperClipIcon class="h-5 w-5 mr-2" />
                            Upload Files
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claims Section -->
            <div
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">File Claims</h3>
                        <Link :href="`/orders/${order.id}/claims`"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-800">
                        View All Claims
                        </Link>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            View and manage all claims associated with files in this order.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
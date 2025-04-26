<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { FolderIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import { PropType, reactive, ref } from 'vue';
// @ts-ignore
import type { PaginatedResponse } from 'vue3-lara-table';

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
    creator: {
        name: string;
    };
    stats?: {
        total: number;
        pending: number;
        claimed: number;
        completed: number;
    };
}

const props = defineProps({
    orders: Object as PropType<PaginatedResponse<OrderData>>,
});

const statusFilter = ref('');

const getStatusClass = (status: string): string => {
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

const getStatusText = (status: string): string => {
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

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const columns = reactive([
    { label: 'Order', key: 'order' },
    { label: 'Status', key: 'status' },
    { label: 'Progress', key: 'progress' },
    { label: 'Created', key: 'created_at' },
    { label: 'Created By', key: 'creator' },
    { label: 'Actions', key: 'actions' },
]);

const filters = reactive([
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        options: [
            { value: '', label: 'All Status' },
            { value: 'pending', label: 'Pending' },
            { value: 'in_progress', label: 'In Progress' },
            { value: 'completed', label: 'Completed' },
            { value: 'approved', label: 'Approved' },
        ],
    },
]);
</script>

<template>
    <AuthenticatedLayout header="Orders" description="Manage your orders">

        <Head title="Orders" />

        <div class="space-y-6">
            <div
                class="overflow-hidden p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
                <LaraTable :columns="columns" :items="props.orders" :filters="filters" search-key="name" enableAddItem
                    :classes="{
                        paginationActiveButton: 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white',
                    }">
                    <template #add-item>
                        <div class="flex justify-end items-center">
                            <Link href="/orders/create"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Create Order
                            </Link>
                        </div>
                    </template>

                    <template #order="{ item }">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-md bg-indigo-100 dark:bg-indigo-900">
                                <FolderIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ item.name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.order_number }}
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #status="{ item }">
                        <span :class="[
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                            getStatusClass(item.status)
                        ]">
                            {{ getStatusText(item.status) }}
                        </span>
                    </template>

                    <template #progress="{ item }">
                        <div v-if="item.stats && item.stats.total > 0" class="w-36">
                            <div class="text-xs mb-1">
                                {{ item.stats.completed }} / {{ item.stats.total }} files
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 dark:bg-indigo-400"
                                    :style="{ width: `${(item.stats.completed / item.stats.total) * 100}%` }">
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 dark:text-gray-400 text-sm">No files</div>
                    </template>

                    <template #created_at="{ item }">
                        {{ formatDate(item.created_at) }}
                    </template>

                    <template #creator="{ item }">
                        {{ item.creator?.name || 'Unknown' }}
                    </template>

                    <template #actions="{ item }">
                        <div class="flex gap-2">
                            <Link :href="`/orders/${item.id}`"
                                class="px-4 py-2 text-sm font-medium bg-indigo-500 text-white rounded-md hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                            View
                            </Link>
                            <Link :href="`/orders/${item.id}/edit`"
                                class="px-4 py-2 text-sm font-medium bg-gray-900 text-white rounded-md hover:bg-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800">
                            Edit
                            </Link>
                        </div>
                    </template>

                    <template #empty-state>
                        <div class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <FolderIcon class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No orders found</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Get started by creating a new order.
                                </p>
                                <Link href="/orders/create"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Create Order
                                </Link>
                            </div>
                        </div>
                    </template>
                </LaraTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
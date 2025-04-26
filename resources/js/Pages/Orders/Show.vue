<script setup lang="ts">
import FileManager from '@/Components/Orders/FileManager.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, PropType, ref } from 'vue';

type StatsData = {
    total: number;
    pending: number;
    claimed: number;
    completed: number;
}

const props = defineProps({
    order: {
        type: Object as PropType<App.Data.OrderData>,
        required: true
    },
    filesGrouped: {
        type: Object as PropType<App.Data.FileItemData[]>,
        required: true
    },
    stats: {
        type: Object as PropType<StatsData>,
        required: true
    }
});

// State for refreshing file data
const isRefreshing = ref(false);

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

// Format status for display
const formatStatus = (status: string): string => {
    const statusMap: Record<string, string> = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };

    return statusMap[status] || status;
};

const canMarkAsCompleted = computed(() => {
    return props.order.status !== 'completed' && props.order.status !== 'approved' && props.stats.total > 0 && props.stats.completed === props.stats.total;
});

const canApprove = computed(() => {
    return props.order.status === 'completed';
});

const markAsCompleted = () => {
    router.post(route('orders.mark-completed', { order: props.order.id }), {}, {
        preserveScroll: true
    });
};

const approveOrder = () => {
    router.post(route('orders.approve', { order: props.order.id }), {}, {
        preserveScroll: true
    });
};

const progressPercentage = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.completed / props.stats.total) * 100);
});

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['filesGrouped', 'stats'],
        onSuccess: () => {
            isRefreshing.value = false;
        },
        onError: () => {
            isRefreshing.value = false;
        }
    });
};
</script>

<template>
    <AuthenticatedLayout header="Order Details" description="View and manage order information">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Order Details
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('orders.edit', order.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit Order
                    </Link>
                    <button v-if="order.status === 'completed'" @click="approveOrder"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Approve Order
                    </button>
                    <button v-else-if="order.status === 'in_progress'" @click="markAsCompleted"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Mark as Completed
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Order Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ order.name }}</h3>
                                <p class="text-sm text-gray-600">Created on {{ formatDate(order.created_at) }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-gray-100 text-gray-800': order.status === 'pending',
                                        'bg-blue-100 text-blue-800': order.status === 'in_progress',
                                        'bg-yellow-100 text-yellow-800': order.status === 'completed',
                                        'bg-green-100 text-green-800': order.status === 'approved',
                                        'bg-red-100 text-red-800': order.status === 'rejected'
                                    }">
                                    {{ formatStatus(order.status) }}
                                </span>
                            </div>
                        </div>

                        <div class="prose max-w-none mb-6">
                            <p>{{ order.description || 'No description provided.' }}</p>
                        </div>

                        <!-- Stats Section -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div v-for="(value, key) in stats" :key="key" class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500">{{ key.charAt(0).toUpperCase() +
                                    key.slice(1) }}
                                    Files</h4>
                                <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Manager -->
                <FileManager :order="order" :filesGrouped="filesGrouped" @filesUpdated="refreshData" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowPathIcon,
    CheckCircleIcon,
    DocumentDuplicateIcon,
    FolderIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';

const stats = [
    {
        name: 'Total Orders',
        value: '24',
        change: '+3 this week',
        changeType: 'positive',
        icon: DocumentDuplicateIcon,
    },
    {
        name: 'Active Orders',
        value: '12',
        change: '2 completed today',
        changeType: 'neutral',
        icon: FolderIcon,
    },
    {
        name: 'Active Employees',
        value: '8',
        change: '4 currently working',
        changeType: 'positive',
        icon: UserGroupIcon,
    },
    {
        name: 'Completion Rate',
        value: '92%',
        change: '+5% from last month',
        changeType: 'positive',
        icon: CheckCircleIcon,
    },
];

const recentOrders = [
    {
        id: 1,
        name: 'Wedding Photos Collection',
        orderNumber: 'ORD-ABC123',
        files: 128,
        status: 'in_progress',
        createdAt: '2023-04-23',
        progress: 65,
    },
    {
        id: 2,
        name: 'Corporate Event Album',
        orderNumber: 'ORD-DEF456',
        files: 93,
        status: 'pending',
        createdAt: '2023-04-22',
        progress: 0,
    },
    {
        id: 3,
        name: 'Product Catalog Images',
        orderNumber: 'ORD-GHI789',
        files: 210,
        status: 'completed',
        createdAt: '2023-04-20',
        progress: 100,
    },
    {
        id: 4,
        name: 'Real Estate Photography',
        orderNumber: 'ORD-JKL012',
        files: 56,
        status: 'approved',
        createdAt: '2023-04-18',
        progress: 100,
    },
];

const recentActivity = [
    {
        user: 'John Doe',
        action: 'claimed 15 files from Wedding Photos Collection',
        time: '10 minutes ago',
        avatar: 'https://ui-avatars.com/api/?name=John+Doe&background=6366f1&color=fff',
    },
    {
        user: 'Jane Smith',
        action: 'completed processing 20 files from Corporate Event Album',
        time: '1 hour ago',
        avatar: 'https://ui-avatars.com/api/?name=Jane+Smith&background=16a34a&color=fff',
    },
    {
        user: 'Admin',
        action: 'created a new order: Real Estate Photography',
        time: '3 hours ago',
        avatar: 'https://ui-avatars.com/api/?name=Admin&background=dc2626&color=fff',
    },
    {
        user: 'Mike Johnson',
        action: 'approved Product Catalog Images order',
        time: '5 hours ago',
        avatar: 'https://ui-avatars.com/api/?name=Mike+Johnson&background=ca8a04&color=fff',
    },
];

const getStatusClass = (status) => {
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

const getStatusText = (status) => {
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
</script>

<template>
    <AuthenticatedLayout header="Order & File Management" description="Manage your orders and file processing workflow">

        <Head title="Order & File Management" />

        <div class="space-y-6">
            <!-- Stats cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.name"
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <component :is="stat.icon" class="h-6 w-6 text-gray-400 dark:text-gray-300"
                                    aria-hidden="true" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                        {{ stat.name }}
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ stat.value }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3 dark:bg-gray-700">
                        <div class="text-sm">
                            <span :class="[
                                stat.changeType === 'positive' ? 'text-green-600 dark:text-green-400' :
                                    stat.changeType === 'negative' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-300'
                            ]">
                                {{ stat.change }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Orders -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-medium text-gray-900 dark:text-white">Recent Orders</h2>
                            <Link href="/orders"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            View all
                            </Link>
                        </div>
                        <div class="mt-6 flow-root">
                            <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                                <li v-for="order in recentOrders" :key="order.id" class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-500 text-white">
                                                <FolderIcon class="h-5 w-5" />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                {{ order.name }}
                                            </p>
                                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                                {{ order.orderNumber }} · {{ order.files }} files
                                            </p>
                                        </div>
                                        <div>
                                            <span :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                getStatusClass(order.status)
                                            ]">
                                                {{ getStatusText(order.status) }}
                                            </span>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <Link :href="`/orders/${order.id}`"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                            View
                                            </Link>
                                        </div>
                                    </div>
                                    <div v-if="order.status === 'in_progress'" class="mt-2">
                                        <div class="relative pt-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span
                                                        class="text-xs font-semibold inline-block text-indigo-600 dark:text-indigo-400">
                                                        {{ order.progress }}% Complete
                                                    </span>
                                                </div>
                                            </div>
                                            <div
                                                class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200 dark:bg-indigo-900">
                                                <div :style="{ width: `${order.progress}%` }"
                                                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <Link href="/orders/create"
                                class="flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            Create New Order
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-medium text-gray-900 dark:text-white">Recent Activity</h2>
                            <Link href="/claims"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            View all claims
                            </Link>
                        </div>
                        <div class="mt-6 flow-root">
                            <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                                <li v-for="(activity, activityIdx) in recentActivity" :key="activityIdx" class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img class="h-8 w-8 rounded-full" :src="activity.avatar" alt="" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                {{ activity.user }}
                                            </p>
                                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                                {{ activity.action }}
                                            </p>
                                        </div>
                                        <div
                                            class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ activity.time }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <button type="button"
                                class="flex w-full items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                <ArrowPathIcon class="mr-2 h-4 w-4" />
                                Refresh Activity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
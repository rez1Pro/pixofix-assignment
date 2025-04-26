<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    ChartBarIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
} from '@heroicons/vue/24/outline';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    BarElement,
    ArcElement
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    BarElement,
    ArcElement
);

const stats = [
    {
        name: 'Total Revenue',
        value: '$45,231.89',
        change: '+20.1%',
        changeType: 'positive',
        icon: CurrencyDollarIcon,
    },
    {
        name: 'Active Users',
        value: '2,338',
        change: '+15.3%',
        changeType: 'positive',
        icon: UsersIcon,
    },
    {
        name: 'New Orders',
        value: '182',
        change: '-3.2%',
        changeType: 'negative',
        icon: ShoppingCartIcon,
    },
    {
        name: 'Growth Rate',
        value: '32.5%',
        change: '+2.4%',
        changeType: 'positive',
        icon: ChartBarIcon,
    },
];

const recentActivities = [
    {
        user: 'John Doe',
        action: 'created a new order',
        time: '2 minutes ago',
        avatar: 'https://ui-avatars.com/api/?name=John+Doe&background=6366f1&color=fff',
    },
    // Add more activities...
];

const topProducts = [
    {
        name: 'Product A',
        sales: 245,
        revenue: '$12,480',
        growth: '+28%',
    },
    // Add more products...
];

// Chart Data
const salesData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
        {
            label: 'Sales',
            data: [30, 45, 35, 50, 40, 60],
            fill: true,
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4
        }
    ]
};

const revenueData = {
    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
    datasets: [
        {
            label: 'Revenue',
            data: [12000, 19000, 15000, 25000],
            backgroundColor: [
                'rgba(79, 70, 229, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderRadius: 6
        }
    ]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                display: false
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout header="Welcome back, Admin!" description="Here's what's happening with your business today.">
        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in stats" :key="stat.name"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
                        </div>
                        <div :class="[
                            'p-3 rounded-lg',
                            stat.changeType === 'positive' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'
                        ]">
                            <component :is="stat.icon" :class="[
                                'w-6 h-6',
                                stat.changeType === 'positive' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                            ]" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span :class="[
                            'text-sm font-medium',
                            stat.changeType === 'positive' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                        ]">
                            {{ stat.change }}
                        </span>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">from last month</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Sales Overview</h2>
                    <div class="mt-4 h-80">
                        <Line :data="salesData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Analytics</h2>
                    <div class="mt-4 h-80">
                        <Bar :data="revenueData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Bottom Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activities -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activities</h2>
                    <div class="mt-4 space-y-4">
                        <div v-for="(activity, index) in recentActivities" :key="index"
                            class="flex items-center space-x-4">
                            <img :src="activity.avatar" :alt="activity.user" class="w-10 h-10 rounded-full" />
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ activity.user }}</span>
                                    {{ activity.action }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h2>
                    <div class="mt-4">
                        <div class="space-y-4">
                            <div v-for="(product, index) in topProducts" :key="index"
                                class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ product.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.sales }} sales</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ product.revenue
                                        }}</p>
                                    <p class="text-xs text-green-600 dark:text-green-400">{{ product.growth }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    ChartBarIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
} from '@heroicons/vue/24/outline';
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
    <AuthenticatedLayout header="Example" description="This is example of this theme">

        <Head title="Example" />

        <div>
            Hello world
        </div>
    </AuthenticatedLayout>
</template>

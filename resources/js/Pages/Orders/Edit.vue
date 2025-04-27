<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PropType } from 'vue';

interface Order {
    id: number;
    order_number: string;
    name: string;
    description: string | null;
    customer_name: string | null;
    deadline: string | null;
    status: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps({
    order: {
        type: Object as PropType<Order>,
        required: true
    }
});

const form = useForm({
    name: props.order.name,
    description: props.order.description || '',
    customer_name: props.order.customer_name || '',
    deadline: props.order.deadline || '',
});

const submitForm = () => {
    form.put(`/orders/${props.order.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Form submitted successfully
        },
    });
};
</script>

<template>
    <AuthenticatedLayout :header="'Edit Order: ' + order.name" description="Update order information">

        <Head :title="'Edit Order: ' + order.name" />

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="submitForm" class="space-y-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow px-4 py-5 sm:p-6">

                    <!-- Order Number (Read-only) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Order Number
                        </label>
                        <div class="mt-1">
                            <p class="text-gray-900 dark:text-white font-medium">{{ order.order_number }}</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Order Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Order Name
                            </label>
                            <div class="mt-1">
                                <input id="name" v-model="form.name" type="text" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter order name" />
                            </div>
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Order Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <div class="mt-1">
                                <textarea id="description" v-model="form.description" rows="3"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter order description"></textarea>
                            </div>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Customer Name -->
                        <div>
                            <label for="customer_name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Customer Name
                            </label>
                            <div class="mt-1">
                                <input id="customer_name" v-model="form.customer_name" type="text"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter customer name" />
                            </div>
                            <p v-if="form.errors.customer_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.customer_name }}
                            </p>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label for="deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Deadline
                            </label>
                            <div class="mt-1">
                                <input id="deadline" v-model="form.deadline" type="date"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                            <p v-if="form.errors.deadline" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.deadline }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form actions -->
                <div class="flex justify-end space-x-3">
                    <Link :href="`/orders/${order.id}`"
                        class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                    </Link>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
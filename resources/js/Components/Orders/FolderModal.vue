<script setup lang="ts">
defineProps<{
    title: string;
    inputLabel: string;
    placeholder: string;
    modelValue: string;
    parentFolder?: string | null;
    showModal: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    cancel: [];
    submit: [];
}>();

const updateValue = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ title }}</h3>

            <p v-if="parentFolder" class="mb-4 text-sm text-gray-600">
                Adding subfolder to: <span class="font-medium">{{ parentFolder }}</span>
            </p>

            <div class="mb-4">
                <label :for="inputLabel" class="block text-sm font-medium text-gray-700 mb-1">{{ inputLabel }}</label>
                <input :id="inputLabel" :value="modelValue" @input="updateValue"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                    :placeholder="placeholder" />
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="$emit('cancel')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Cancel
                </button>
                <button @click="$emit('submit')"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Create Folder
                </button>
            </div>
        </div>
    </div>
</template>
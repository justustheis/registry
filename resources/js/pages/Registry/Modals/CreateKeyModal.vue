<template>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="$emit('close')"
            ></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <!-- Header -->
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Create Registry Entry
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Create a new registry entry with a key-value pair.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="mt-5 space-y-4">
                    <!-- Key -->
                    <div>
                        <label for="key" class="block text-sm font-medium text-gray-700 mb-1">
                            Key <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.key"
                            type="text"
                            id="key"
                            name="key"
                            required
                            placeholder="e.g., app.name, user.settings.theme"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.key ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        />
                        <p v-if="errors.key" class="mt-1 text-xs text-red-600">{{ errors.key }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Use dot notation for hierarchical keys (e.g., app.database.host)
                        </p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Type
                        </label>
                        <select
                            v-model="form.type"
                            id="type"
                            name="type"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.type ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        >
                            <option value="auto">Auto</option>
                            <option value="string">String</option>
                            <option value="integer">Integer</option>
                            <option value="float">Float</option>
                            <option value="boolean">Boolean</option>
                            <option value="array">Array</option>
                            <option value="object">Object</option>
                        </select>
                        <p v-if="errors.type" class="mt-1 text-xs text-red-600">{{ errors.type }}</p>
                        <p class="mt-1 text-xs text-blue-600">
                            💡 Specifying type improves read performance
                        </p>
                    </div>

                    <!-- Value -->
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-1">
                            Value
                        </label>

                        <!-- Auto/String/Integer/Float -->
                        <input
                            v-if="form.type === 'auto' || form.type === 'string' || form.type === 'integer' || form.type === 'float'"
                            v-model="form.value"
                            :type="form.type === 'integer' || form.type === 'float' ? 'number' : 'text'"
                            :step="form.type === 'float' ? 'any' : '1'"
                            id="value"
                            name="value"
                            :placeholder="getPlaceholder()"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.value ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        />

                        <!-- Boolean -->
                        <select
                            v-else-if="form.type === 'boolean'"
                            v-model="form.value"
                            id="value"
                            name="value"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.value ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        >
                            <option value="">Select a value</option>
                            <option value="true">True</option>
                            <option value="false">False</option>
                        </select>

                        <!-- Array/Object -->
                        <textarea
                            v-else-if="form.type === 'array' || form.type === 'object'"
                            v-model="form.value"
                            id="value"
                            name="value"
                            rows="4"
                            :placeholder="getPlaceholder()"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.value ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        ></textarea>

                        <p v-if="errors.value" class="mt-1 text-xs text-red-600">{{ errors.value }}</p>
                        <p v-if="form.type === 'array' || form.type === 'object'" class="mt-1 text-xs text-gray-500">
                            Enter valid JSON format
                        </p>
                    </div>

                    <!-- Encrypted -->
                    <div class="flex items-center">
                        <input
                            v-model="form.encrypted"
                            type="checkbox"
                            id="encrypted"
                            name="encrypted"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="encrypted" class="ml-2 block text-sm text-gray-700">
                            Encrypt this value
                        </label>
                    </div>
                </form>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="handleSubmit"
                        :disabled="processing"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Creating...' : 'Create Entry' }}
                    </button>
                    <button
                        @click="$emit('close')"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3'
import { store } from '../../../routes/registry'

export default {
    name: 'CreateKeyModal',
    emits: ['close', 'created'],
    data() {
        return {
            form: {
                key: '',
                value: '',
                type: 'auto',
                encrypted: false,
            },
            processing: false,
            errors: {},
        }
    },
    methods: {
        getPlaceholder() {
            switch (this.form.type) {
                case 'auto':
                    return 'Enter any value (type will be auto-detected)'
                case 'string':
                    return 'Enter string value'
                case 'integer':
                    return 'Enter integer value'
                case 'float':
                    return 'Enter decimal value'
                case 'array':
                    return '["item1", "item2", "item3"]'
                case 'object':
                    return '{"key": "value", "nested": {"key": "value"}}'
                default:
                    return ''
            }
        },
        validateForm() {
            this.errors = {}

            if (!this.form.key) {
                this.errors.key = 'Key is required'
            } else if (!/^[a-zA-Z0-9._-]+$/.test(this.form.key)) {
                this.errors.key = 'Key can only contain letters, numbers, dots, hyphens, and underscores'
            }


            if (this.form.type === 'array' || this.form.type === 'object') {
                if (this.form.value) {
                    try {
                        JSON.parse(this.form.value)
                    } catch (e) {
                        this.errors.value = 'Invalid JSON format'
                    }
                }
            }

            return Object.keys(this.errors).length === 0
        },
        handleSubmit() {
            if (!this.validateForm()) {
                return
            }

            this.processing = true

            let processedValue = this.form.value

            // Process value based on type
            if (this.form.type === 'boolean') {
                processedValue = this.form.value === 'true'
            } else if (this.form.type === 'integer') {
                processedValue = parseInt(this.form.value, 10)
            } else if (this.form.type === 'float') {
                processedValue = parseFloat(this.form.value)
            } else if (this.form.type === 'array' || this.form.type === 'object') {
                try {
                    processedValue = this.form.value ? JSON.parse(this.form.value) : (this.form.type === 'array' ? [] : {})
                } catch (e) {
                    this.errors.value = 'Invalid JSON format'
                    this.processing = false
                    return
                }
            }

            router.post(store().url, {
                key: this.form.key,
                value: processedValue,
                type: this.form.type === 'auto' ? null : this.form.type,
                encrypted: this.form.encrypted,
            }, {
                onSuccess: () => {
                    this.$emit('created')
                },
                onError: (errors) => {
                    this.errors = errors
                },
                onFinish: () => {
                    this.processing = false
                },
            })
        },
    },
}
</script>

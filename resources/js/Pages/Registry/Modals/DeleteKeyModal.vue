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
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Delete Registry Entry
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this registry entry? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Entry details -->
                <div v-if="entry" class="mt-4 bg-gray-50 border border-gray-200 rounded-md p-3">
                    <div class="space-y-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Key:</span>
                            <code class="ml-2 text-sm font-mono bg-white px-2 py-0.5 border border-gray-200 rounded">{{ entry.key }}</code>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Type:</span>
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="getTypeColorClass(entry.type)"
                            >
                                {{ entry.type || 'auto' }}
                            </span>
                            <span 
                                v-if="entry.encrypted"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Encrypted
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Value:</span>
                            <div class="mt-1 text-sm text-gray-600 font-mono bg-white p-2 border border-gray-200 rounded max-h-20 overflow-y-auto">
                                {{ formatValue(entry.value, entry.type) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click.prevent="handleDelete"
                        :disabled="processing"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Deleting...' : 'Delete Entry' }}
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
import { destroy } from '@/routes/registry'

export default {
    name: 'DeleteKeyModal',
    props: {
        entry: {
            type: Object,
            required: true,
        },
    },
    emits: ['close', 'deleted'],
    data() {
        return {
            processing: false,
        }
    },
    methods: {
        formatValue(value, type) {
            if (value == null) return ''
            
            switch (type) {
                case 'string':
                    return String(value)
                case 'integer':
                case 'float':
                    return String(value)
                case 'boolean':
                    return value ? 'true' : 'false'
                case 'array':
                case 'object':
                    try {
                        return typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value)
                    } catch {
                        return String(value)
                    }
                default:
                    return String(value)
            }
        },
        getTypeColorClass(type) {
            const typeMap = {
                string: 'bg-blue-100 text-blue-800',
                integer: 'bg-green-100 text-green-800',
                float: 'bg-green-100 text-green-800',
                boolean: 'bg-purple-100 text-purple-800',
                array: 'bg-orange-100 text-orange-800',
                object: 'bg-red-100 text-red-800',
            }
            return typeMap[type] || 'bg-gray-100 text-gray-800'
        },
        handleDelete() {
            this.processing = true
            
            // Emit deleted event
            this.$emit('deleted')
            
            const deleteRoute = destroy(this.entry.key)
            
            router.visit(deleteRoute.url, {
                method: deleteRoute.method.toLowerCase(),
                preserveState: false,
                onError: () => {
                    this.processing = false
                },
                onFinish: () => {
                    this.processing = false
                },
            })
        },
    },
}
</script>
<template>
    <div class="entry-detail">
        <div v-if="entry" class="p-6">
            <!-- Entry Information -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <h3 class="text-lg font-medium text-gray-900">Registry Entry Details</h3>
                </div>

                <!-- Content -->
                <div class="p-4 space-y-4">
                    <!-- Key -->
                    <div class="grid grid-cols-1 gap-1">
                        <label class="text-sm font-medium text-gray-700">Key</label>
                        <div class="flex items-center space-x-2">
                            <code class="flex-1 px-3 py-2 text-sm font-mono bg-gray-50 border border-gray-200 rounded-md">
                                {{ entry.key }}
                            </code>
                            <button
                                @click="copyToClipboard(entry.key)"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                title="Copy key to clipboard"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Value -->
                    <div class="grid grid-cols-1 gap-1">
                        <label class="text-sm font-medium text-gray-700">Value</label>
                        <div class="relative">
                            <div
                                v-if="entry.type === 'string' || entry.type === 'integer' || entry.type === 'float'"
                                class="min-h-20 px-3 py-2 text-sm bg-white border border-gray-200 rounded-md font-mono break-all"
                            >
                                {{ formatValue(entry.value, entry.type) }}
                            </div>
                            <div
                                v-else-if="entry.type === 'boolean'"
                                class="flex items-center px-3 py-2 text-sm bg-white border border-gray-200 rounded-md"
                            >
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        entry.value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ entry.value ? 'True' : 'False' }}
                                </span>
                            </div>
                            <div
                                v-else
                                class="min-h-32 px-3 py-2 text-sm bg-white border border-gray-200 rounded-md font-mono"
                            >
                                <pre class="whitespace-pre-wrap break-all">{{ formatComplexValue(entry.value) }}</pre>
                            </div>
                            <button
                                @click="copyToClipboard(formatValue(entry.value, entry.type))"
                                class="absolute top-2 right-2 p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                title="Copy value to clipboard"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Type -->
                        <div class="grid grid-cols-1 gap-1">
                            <label class="text-sm font-medium text-gray-700">Type</label>
                            <div class="flex items-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        getTypeColorClass(entry.type)
                                    ]"
                                >
                                    {{ entry.type || 'auto' }}
                                </span>
                            </div>
                        </div>

                        <!-- Encrypted -->
                        <div class="grid grid-cols-1 gap-1">
                            <label class="text-sm font-medium text-gray-700">Encrypted</label>
                            <div class="flex items-center">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        entry.encrypted ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    <svg v-if="entry.encrypted" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    {{ entry.encrypted ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Scope (for scoped entries) -->
                    <div v-if="entry.is_scoped" class="grid grid-cols-1 gap-1">
                        <label class="text-sm font-medium text-gray-700">Scope</label>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                {{ formatScope(entry.registrable_type, entry.registrable_id) }}
                            </span>
                            <div class="text-xs text-gray-500">
                                Original key: <code class="bg-gray-100 px-1 py-0.5 rounded">{{ entry.original_key }}</code>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="grid grid-cols-1 gap-1">
                        <label class="text-sm font-medium text-gray-700">Last Updated</label>
                        <div class="text-sm text-gray-600">
                            {{ formatDate(entry.updated_at) }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            @click="$emit('edit')"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Value
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No selection state -->
        <div v-else class="flex flex-col items-center justify-center h-64 text-gray-500 p-6">
            <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-400 mb-2">No Entry Selected</h3>
            <p class="text-sm text-gray-400 text-center">
                Select a registry key from the tree view on the left to see its details here.
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EntryDetail',
    props: {
        entry: {
            type: Object,
            default: null,
        },
    },
    emits: ['edit'],
    methods: {
        formatValue(value, type) {
            if (value === null || value === undefined) return ''
            
            switch (type) {
                case 'string':
                    return String(value)
                case 'integer':
                case 'float':
                    return String(value)
                case 'boolean':
                    return value ? 'true' : 'false'
                default:
                    return String(value)
            }
        },
        formatComplexValue(value) {
            if (value === null || value === undefined) return ''
            
            try {
                if (typeof value === 'object') {
                    return JSON.stringify(value, null, 2)
                }
                return String(value)
            } catch (e) {
                return String(value)
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'Unknown'
            
            try {
                return new Date(dateString).toLocaleString()
            } catch (e) {
                return dateString
            }
        },
        getTypeColorClass(type) {
            const typeColors = {
                string: 'bg-blue-100 text-blue-800',
                integer: 'bg-green-100 text-green-800',
                float: 'bg-green-100 text-green-800',
                boolean: 'bg-purple-100 text-purple-800',
                array: 'bg-orange-100 text-orange-800',
                object: 'bg-red-100 text-red-800',
            }
            return typeColors[type] || 'bg-gray-100 text-gray-800'
        },
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text)
                // You might want to show a toast notification here
                console.log('Copied to clipboard:', text)
            } catch (err) {
                console.error('Failed to copy to clipboard:', err)
                // Fallback for older browsers
                const textArea = document.createElement('textarea')
                textArea.value = text
                document.body.appendChild(textArea)
                textArea.select()
                document.execCommand('copy')
                document.body.removeChild(textArea)
            }
        },
        formatScope(registrableType, registrableId) {
            if (!registrableType || !registrableId) return 'Global'
            const modelName = registrableType.split('\\').pop() // Get class name from namespace
            return `${modelName}#${registrableId}`
        },
    },
}
</script>
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
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Rename Registry Key
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Rename the registry key. This will also affect any child keys if you choose to rename them.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="mt-5 space-y-4">
                    <!-- Scope Info (for scoped entries) -->
                    <div v-if="entry?.is_scoped" class="bg-purple-50 border border-purple-200 rounded-md p-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-medium text-purple-800">
                                Scoped to {{ formatScope(entry?.registrable_type, entry?.registrable_id) }}
                            </span>
                        </div>
                        <p class="text-xs text-purple-600 mt-1">
                            You are renaming the key within this scope. The hierarchical path is {{ entry?.key }}
                        </p>
                    </div>

                    <!-- Current Key -->
                    <div>
                        <label for="current-key" class="block text-sm font-medium text-gray-700 mb-1">
                            Current Key
                        </label>
                        <input
                            :value="currentKeyToShow"
                            type="text"
                            id="current-key"
                            name="current-key"
                            disabled
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm"
                        />
                    </div>

                    <!-- New Key -->
                    <div>
                        <label for="new-key" class="block text-sm font-medium text-gray-700 mb-1">
                            New Key <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.new_key"
                            type="text"
                            id="new-key"
                            name="new-key"
                            required
                            placeholder="Enter the new key name"
                            :class="[
                                'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm',
                                errors.new_key ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                            ]"
                        />
                        <p v-if="errors.new_key" class="mt-1 text-xs text-red-600">{{ errors.new_key }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Use dot notation for hierarchical keys (e.g., app.database.host)
                        </p>
                    </div>

                    <!-- Rename Children Option -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                v-model="form.rename_children"
                                type="checkbox"
                                id="rename-children"
                                name="rename-children"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="rename-children" class="font-medium text-gray-700">
                                Rename child keys as well
                            </label>
                            <p class="text-gray-500">
                                If checked, all keys that start with the current key will also be renamed to match the new key structure.
                            </p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div v-if="form.new_key && hasChildKeys" class="bg-gray-50 border border-gray-200 rounded-md p-3">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Preview of changes:</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex items-center">
                                <span class="text-gray-500 mr-2">→</span>
                                <code class="text-red-600">{{ currentKeyToShow }}</code>
                                <span class="mx-2">→</span>
                                <code class="text-green-600">{{ form.new_key }}</code>
                            </div>
                            <div v-if="form.rename_children && childKeyCount > 0" class="text-xs text-gray-500 ml-4">
                                + {{ childKeyCount }} child key{{ childKeyCount > 1 ? 's' : '' }} will also be renamed
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="handleSubmit"
                        :disabled="processing || !form.new_key"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Renaming...' : 'Rename Key' }}
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
import { rename } from '../../routes/registry'

export default {
    name: 'RenameKeyModal',
    props: {
        entry: {
            type: Object,
            required: true,
        },
        entries: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['close', 'renamed'],
    data() {
        return {
            form: {
                new_key: '',
                rename_children: true,
            },
            processing: false,
            errors: {},
        }
    },
    computed: {
        currentKeyToShow() {
            // For scoped entries, show the original key, not the hierarchical key
            return this.entry?.original_key || this.entry?.key
        },
        hasChildKeys() {
            if (!this.entry || !this.$page?.props?.entries) return false
            const keyToCheck = this.entry.original_key || this.entry.key
            return this.$page.props.entries.some(e => {
                const compareKey = e.original_key || e.key
                return compareKey !== keyToCheck && compareKey.startsWith(keyToCheck + '.')
            })
        },
        childKeyCount() {
            if (!this.entry || !this.$page?.props?.entries) return 0
            const keyToCheck = this.entry.original_key || this.entry.key
            return this.$page.props.entries.filter(e => {
                const compareKey = e.original_key || e.key
                return compareKey !== keyToCheck && compareKey.startsWith(keyToCheck + '.')
            }).length
        },
    },
    mounted() {
        if (this.entry) {
            // Use original key for scoped entries, hierarchical key for global entries
            this.form.new_key = this.entry.original_key || this.entry.key
        }
    },
    methods: {
        validateForm() {
            this.errors = {}

            if (!this.form.new_key) {
                this.errors.new_key = 'New key is required'
            } else if (!/^[a-zA-Z0-9._-]+$/.test(this.form.new_key)) {
                this.errors.new_key = 'Key can only contain letters, numbers, dots, hyphens, and underscores'
            } else if (this.form.new_key === (this.entry.original_key || this.entry.key)) {
                this.errors.new_key = 'New key must be different from the current key'
            }

            return Object.keys(this.errors).length === 0
        },
        handleSubmit() {
            if (!this.validateForm()) {
                return
            }

            this.processing = true

            router.patch(rename(this.entry.key).url, {
                new_key: this.form.new_key,
                rename_children: this.form.rename_children,
            }, {
                onSuccess: () => {
                    this.$emit('renamed')
                },
                onError: (errors) => {
                    this.errors = errors
                },
                onFinish: () => {
                    this.processing = false
                },
            })
        },
        formatScope(registrableType, registrableId) {
            if (!registrableType || !registrableId) return 'Global'
            const modelName = registrableType.split('\\').pop() // Get class name from namespace
            return `${modelName}#${registrableId}`
        },
    },
}
</script>

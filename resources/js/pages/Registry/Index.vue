<template>
    <AppLayout>
        <div class="flex h-screen bg-white">
            <!-- Left Pane - Tree View -->
            <div class="w-1/3 min-w-80 border-r border-gray-200 flex flex-col">
                <!-- Toolbar -->
                <div class="border-b border-gray-200 p-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-medium text-gray-700">Registry Keys</h2>
                        <div class="flex space-x-1">
                            <button
                                @click="showCreateKeyModal = true"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                title="Create New Key"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <button
                                @click="refreshEntries"
                                :disabled="isRefreshing"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded disabled:opacity-50"
                                title="Refresh"
                            >
                                <svg
                                    :class="['w-4 h-4', { 'animate-spin': isRefreshing }]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Search -->
                    <div class="mt-2">
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Search keys..."
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Tree View -->
                <div class="flex-1 overflow-auto">
                    <TreeView
                        :tree="filteredTree"
                        :selected-key="activeSelectedKey"
                        :expanded-keys="expandedKeys"
                        @select="handleSelectKey"
                        @expand="handleExpandKey"
                        @context-menu="handleContextMenu"
                    />
                </div>
            </div>

            <!-- Right Pane - Entry Details -->
            <div class="flex-1 flex flex-col">
                <!-- Toolbar -->
                <div class="border-b border-gray-200 p-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-medium text-gray-700">
                            {{ selectedEntry ? selectedEntry.key : 'No key selected' }}
                        </h2>
                        <div class="flex space-x-1" v-if="selectedEntry">
                            <button
                                @click="editEntry"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                title="Edit Value"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button
                                @click="renameEntry"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                title="Rename Key"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button
                                @click="deleteEntry"
                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded"
                                title="Delete Key"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Entry Detail -->
                <div class="flex-1 overflow-auto">
                    <EntryDetail
                        :entry="selectedEntry"
                        @edit="editEntry"
                    />
                </div>
            </div>
        </div>

        <!-- Modals -->
        <CreateKeyModal
            v-if="showCreateKeyModal"
            @close="showCreateKeyModal = false"
            @created="showCreateKeyModal = false"
        />

        <RenameKeyModal
            v-if="showRenameKeyModal"
            :entry="selectedEntry"
            @close="showRenameKeyModal = false"
            @renamed="handleKeyRenamed"
        />

        <EditValueModal
            v-if="showEditValueModal"
            :entry="selectedEntry"
            @close="showEditValueModal = false"
            @updated="handleValueUpdated"
        />

        <DeleteKeyModal
            v-if="showDeleteKeyModal"
            :entry="selectedEntry"
            @close="showDeleteKeyModal = false"
            @deleted="handleKeyDeleted"
        />
    </AppLayout>
</template>

<script>
import AppLayout from '../../Layouts/AppLayout.vue'
import TreeView from '../..//Components/Registry/TreeView.vue'
import EntryDetail from '../../Components/Registry/EntryDetail.vue'
import CreateKeyModal from './Modals/CreateKeyModal.vue'
import RenameKeyModal from './Modals/RenameKeyModal.vue'
import EditValueModal from './Modals/EditValueModal.vue'
import DeleteKeyModal from './Modals/DeleteKeyModal.vue'
import { router } from '@inertiajs/vue3'

export default {
    name: 'RegistryIndex',
    components: {
        AppLayout,
        TreeView,
        EntryDetail,
        CreateKeyModal,
        RenameKeyModal,
        EditValueModal,
        DeleteKeyModal,
    },
    props: {
        entries: {
            type: Array,
            required: true,
        },
        tree: {
            type: Array,
            required: true,
        },
        selectedKey: {
            type: String,
            default: null,
        },
        expandedKey: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            currentSelectedKey: null,
            searchTerm: '',
            expandedKeys: new Set(),
            showCreateKeyModal: false,
            showRenameKeyModal: false,
            showEditValueModal: false,
            showDeleteKeyModal: false,
            contextMenuKey: null,
            isRefreshing: false,
        }
    },
    watch: {
        searchTerm: {
            handler(newTerm, oldTerm) {
                if (newTerm) {
                    this.handleSearch(newTerm)
                } else {
                    // Reset expansion state when search is cleared
                    this.expandedKeys.clear()
                }
            },
            immediate: false,
        },
    },
    computed: {
        activeSelectedKey() {
            return this.currentSelectedKey || this.selectedKey
        },
        selectedEntry() {
            if (!this.activeSelectedKey) return null
            return this.entries.find(entry => entry.key === this.activeSelectedKey)
        },
        filteredTree() {
            if (!this.searchTerm) return this.tree
            return this.filterTree(this.tree, this.searchTerm.toLowerCase(), true)
        },
    },
    mounted() {
        // Auto-select key if specified as prop on initial load
        if (this.selectedKey) {
            this.currentSelectedKey = this.selectedKey;
            // Expand parent folders for the selected key
            this.expandParentFolders(this.selectedKey);
        }
    },
    watch: {
        selectedKey: {
            handler(newKey) {
                if (newKey) {
                    this.currentSelectedKey = newKey;
                    // Expand parent folders for the selected key
                    this.expandParentFolders(newKey);
                }
            },
            immediate: true
        },
        expandedKey: {
            handler(newKey) {
                if (newKey) {
                    // Just expand to the key, don't select it
                    this.expandParentFolders(newKey);
                }
            },
            immediate: true
        }
    },
    methods: {
        handleSelectKey(key) {
            this.currentSelectedKey = key
        },
        handleExpandKey(key) {
            if (this.expandedKeys.has(key)) {
                this.expandedKeys.delete(key)
            } else {
                this.expandedKeys.add(key)
            }
        },
        handleContextMenu(key) {
            this.contextMenuKey = key
            this.currentSelectedKey = key
        },
        editEntry() {
            if (this.selectedEntry) {
                this.showEditValueModal = true
            }
        },
        renameEntry() {
            if (this.selectedEntry) {
                this.showRenameKeyModal = true
            }
        },
        deleteEntry() {
            if (this.selectedEntry) {
                this.showDeleteKeyModal = true
            }
        },
        refreshEntries() {
            this.isRefreshing = true
            router.reload({
                onFinish: () => {
                    this.isRefreshing = false
                }
            })
        },
        handleKeyRenamed() {
            this.showRenameKeyModal = false
            this.isRefreshing = true
            router.reload({
                onFinish: () => {
                    this.isRefreshing = false
                }
            })
        },
        handleValueUpdated() {
            this.showEditValueModal = false
            this.isRefreshing = true
            router.reload({
                onFinish: () => {
                    this.isRefreshing = false
                }
            })
        },
        handleKeyDeleted() {
            this.showDeleteKeyModal = false
            // Tree expansion is now handled by backend redirect with 'expanded' parameter
        },
        handleSearch(searchTerm) {
            const lowerSearchTerm = searchTerm.toLowerCase()

            // Find all matching entries
            const matchingEntries = this.entries.filter(entry =>
                entry.key.toLowerCase().includes(lowerSearchTerm) ||
                entry.key.split('.').pop().toLowerCase().includes(lowerSearchTerm)
            )

            // Auto-select if there's only one result
            if (matchingEntries.length === 1) {
                this.currentSelectedKey = matchingEntries[0].key
                // Expand parent folders for the selected entry
                this.expandParentFolders(matchingEntries[0].key)
            } else if (matchingEntries.length > 1) {
                // Expand parent folders for all matching entries
                matchingEntries.forEach(entry => {
                    this.expandParentFolders(entry.key)
                })
            }
        },

        expandParentFolders(key) {
            const parts = key.split('.')
            let currentPath = ''

            // Create a new Set with existing keys plus the new ones for reactivity
            const newExpandedKeys = new Set(this.expandedKeys)

            // Expand all parent folders
            for (let i = 0; i < parts.length - 1; i++) {
                if (currentPath) {
                    currentPath += '.' + parts[i]
                } else {
                    currentPath = parts[i]
                }
                newExpandedKeys.add(currentPath)
            }

            // If this is a folder key (has children), expand it too
            const hasChildren = this.entries.some(entry =>
                entry.key !== key && entry.key.startsWith(key + '.')
            )
            if (hasChildren) {
                newExpandedKeys.add(key)
            }

            // Replace the entire Set to trigger reactivity
            this.expandedKeys = newExpandedKeys
        },

        filterTree(tree, searchTerm, shouldExpand = false) {
            return tree.filter(node => {
                const matches = node.key.toLowerCase().includes(searchTerm) ||
                               node.name.toLowerCase().includes(searchTerm)

                if (matches) return true

                if (node.children && node.children.length > 0) {
                    const filteredChildren = this.filterTree(node.children, searchTerm, shouldExpand)
                    if (filteredChildren.length > 0) {
                        return {
                            ...node,
                            children: filteredChildren,
                            expanded: true,
                        }
                    }
                }

                return false
            }).map(node => {
                if (typeof node === 'boolean') return null

                const hasMatches = node.key.toLowerCase().includes(searchTerm) ||
                                 node.name.toLowerCase().includes(searchTerm) ||
                                 (node.children && node.children.length > 0)

                return {
                    ...node,
                    children: node.children ? this.filterTree(node.children, searchTerm, shouldExpand) : [],
                    expanded: shouldExpand && hasMatches ? true : node.expanded,
                }
            }).filter(Boolean)
        },
    },
}
</script>

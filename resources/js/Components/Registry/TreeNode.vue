<template>
    <div class="tree-node">
        <div
            :class="[
                'flex items-center px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 relative',
                { 'bg-blue-50': isSelected }
            ]"
            :style="{ paddingLeft: `${level * 16 + 8}px` }"
            @click="handleClick"
            @contextmenu.prevent="handleContextMenu"
        >
            <!-- Blue selection indicator -->
            <div
                v-if="isSelected"
                class="absolute right-0 top-0 bottom-0 w-0.5 bg-blue-500"
            ></div>
            <!-- Expand/Collapse Icon -->
            <div class="w-4 h-4 mr-1 flex items-center justify-center">
                <button
                    v-if="node.hasChildren"
                    @click.stop="handleToggle"
                    class="w-3 h-3 flex items-center justify-center hover:bg-gray-200 rounded"
                >
                    <svg
                        :class="['w-2.5 h-2.5 text-gray-600 transition-transform', { 'rotate-90': isExpanded }]"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Folder/File Icon -->
            <div class="w-4 h-4 mr-2 flex items-center justify-center">
                <svg
                    v-if="node.hasChildren"
                    :class="['w-4 h-4', isExpanded ? 'text-blue-500' : 'text-gray-500']"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                </svg>
                <svg
                    v-else
                    class="w-4 h-4 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>

            <!-- Node Name -->
            <span
                :class="[
                    'flex-1 truncate',
                    { 'font-semibold text-blue-700': isSelected },
                    { 'text-gray-900': !isSelected && !node.entry },
                    { 'text-gray-700': !isSelected && node.entry }
                ]"
                :title="node.key"
            >
                {{ node.name }}
            </span>

            <!-- Entry Indicator -->
            <div v-if="node.entry" class="flex items-center space-x-1">
                <span
                    v-if="node.entry.is_scoped"
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                    title="Scoped Entry"
                >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </span>
                <span
                    v-if="node.entry.encrypted"
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                    title="Encrypted"
                >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                    :title="`Type: ${node.entry.type}`"
                >
                    {{ getTypeAbbreviation(node.entry.type) }}
                </span>
            </div>
        </div>

        <!-- Children -->
        <div v-if="isExpanded && node.children && node.children.length > 0">
            <TreeNode
                v-for="child in node.children"
                :key="child.key"
                :node="child"
                :level="level + 1"
                :selected-key="selectedKey"
                :expanded-keys="expandedKeys"
                @select="$emit('select', $event)"
                @expand="$emit('expand', $event)"
                @context-menu="$emit('context-menu', $event)"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: 'TreeNode',
    props: {
        node: {
            type: Object,
            required: true,
        },
        level: {
            type: Number,
            default: 0,
        },
        selectedKey: {
            type: String,
            default: null,
        },
        expandedKeys: {
            type: Set,
            default: () => new Set(),
        },
    },
    emits: ['select', 'expand', 'context-menu'],
    computed: {
        isSelected() {
            return this.selectedKey === this.node.key
        },
        isExpanded() {
            return this.expandedKeys.has(this.node.key) || this.node.expanded
        },
    },
    methods: {
        handleClick() {
            this.$emit('select', this.node.key)
            // Also expand/collapse if it's a folder with children
            if (this.node.hasChildren) {
                this.handleToggle()
            }
        },
        handleToggle() {
            this.$emit('expand', this.node.key)
        },
        handleContextMenu() {
            this.$emit('context-menu', this.node.key)
        },
        getTypeAbbreviation(type) {
            if (!type || typeof type !== 'string') {
                return 'AUT'
            }
            const typeMap = {
                string: 'STR',
                integer: 'INT',
                float: 'FLT',
                boolean: 'BOL',
                bool: 'BOL',
                array: 'ARR',
                object: 'OBJ',
            }
            return typeMap[type] || type.substring(0, 4).toUpperCase()
        },
    },
}
</script>

<template>
    <div class="tree-view">
        <div v-if="tree && tree.length > 0" class="py-2">
            <TreeNode
                v-for="node in tree"
                :key="node.key"
                :node="node"
                :level="0"
                :selected-key="selectedKey"
                :expanded-keys="expandedKeys"
                @select="$emit('select', $event)"
                @expand="$emit('expand', $event)"
                @context-menu="$emit('context-menu', $event)"
            />
        </div>

        <div v-else class="flex flex-col items-center justify-center h-64 text-gray-500">
            <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <p class="text-sm text-gray-400 text-center mb-4">
                No registry entries found
            </p>
        </div>
    </div>
</template>

<script>
import TreeNode from './TreeNode.vue'

export default {
    name: 'TreeView',
    components: {
        TreeNode,
    },
    props: {
        tree: {
            type: Array,
            required: true,
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
    emits: ['select', 'expand', 'context-menu', 'create'],
}
</script>

<style scoped>
.tree-view {
    @apply select-none;
}

.tree-view ::-webkit-scrollbar {
    width: 6px;
}

.tree-view ::-webkit-scrollbar-track {
    @apply bg-gray-100;
}

.tree-view ::-webkit-scrollbar-thumb {
    @apply bg-gray-300 rounded-full;
}

.tree-view ::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400;
}
</style>

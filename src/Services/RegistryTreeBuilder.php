<?php

namespace JustusTheis\Registry\Services;

use Illuminate\Support\Collection;

class RegistryTreeBuilder
{
    /*
    |--------------------------------------------------------------------------
    | Registry Tree Builder Service
    |--------------------------------------------------------------------------
    |
    | Converts flat registry entries into hierarchical tree structures for
    | frontend display. Handles nested keys using dot notation and provides
    | expansion state management for navigation.
    |
    */

    /**
     * Build a hierarchical tree structure from registry entries.
     *
     * Static entry point that creates an instance and builds the tree.
     *
     * @param  Collection|array $entries     The registry entries to process
     * @param  string|null      $selectedKey Optional key to auto-expand path to
     * @return array            The hierarchical tree structure
     */
    public static function handle(Collection|array $entries, ?string $selectedKey = null): array
    {
        return (new self())->build($entries, $selectedKey);
    }

    /**
     * Build the tree structure from entries.
     *
     * Processes flat registry entries with dot-notation keys into a nested
     * tree structure suitable for frontend tree components.
     *
     * @param  Collection|array $entries     The entries to process
     * @param  string|null      $selectedKey Key to auto-expand path to
     * @return array            The built tree structure
     */
    public function build(Collection|array $entries, ?string $selectedKey = null): array
    {
        $items = $entries instanceof Collection ? $entries : collect($entries);

        if ($items->isEmpty()) {
            return [];
        }

        $tree = [];

        foreach ($items as $entry) {
            if (! is_array($entry)) {
                if (is_object($entry) && method_exists($entry, 'toArray')) {
                    $entry = $entry->toArray();
                } else {
                    continue;
                }
            }

            if (! isset($entry['key']) || ! is_string($entry['key']) || $entry['key'] === '') {
                continue;
            }

            $parts = explode('.', $entry['key']);
            $current = &$tree;
            $currentPath = '';
            $lastIndex = count($parts) - 1;

            foreach ($parts as $i => $part) {
                $part = trim($part);
                if ($part === '') {
                    continue; // ignore empty segments
                }

                $currentPath = $currentPath === '' ? $part : $currentPath.'.'.$part;

                if (! isset($current[$part])) {
                    $current[$part] = [
                        'key'         => $currentPath,
                        'name'        => $part,
                        'children'    => [],
                        'hasChildren' => false,
                        'isLeaf'      => false,
                        'entry'       => null,
                        'expanded'    => false,
                    ];
                }

                if ($i === $lastIndex) {
                    // Attach payload on leaf
                    $current[$part]['entry'] = $entry;
                    $current[$part]['isLeaf'] = true;
                } else {
                    $current[$part]['hasChildren'] = true;
                }

                $current = &$current[$part]['children'];
            }
        }

        if ($selectedKey) {
            $this->expandPath($tree, $selectedKey);
        }

        return $this->toArray($tree);
    }

    /**
     * Expand the tree path to make a specific key visible.
     *
     * Traverses the tree structure and marks all parent folders as expanded
     * to ensure the specified key is visible in the UI.
     *
     * @param  array  $tree        The tree structure to modify (by reference)
     * @param  string $selectedKey The key path to expand to
     * @return void
     */
    private function expandPath(array &$tree, string $selectedKey): void
    {
        $segments = array_values(array_filter(explode('.', $selectedKey), fn ($s) => $s !== ''));
        $current = &$tree;

        foreach ($segments as $i => $segment) {
            if (! isset($current[$segment])) {
                break; // path not found; stop gracefully
            }

            $current[$segment]['expanded'] = true;

            $current = &$current[$segment]['children'];
        }
    }

    /**
     * Convert the internal tree map to a sorted array structure.
     *
     * Recursively processes the tree map to create the final array structure
     * with proper sorting and children count updates.
     *
     * @param  array $map The internal tree map
     * @return array The final sorted tree array
     */
    private function toArray(array $map): array
    {
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        $out = [];
        foreach ($map as $node) {
            $children = $this->toArray($node['children']);

            $node['children'] = $children;
            $node['hasChildren'] = ! empty($children);

            $out[] = $node;
        }

        return $out;
    }
}

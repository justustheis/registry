<?php

namespace JustusTheis\Registry\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JustusTheis\Registry\Registry;
use Illuminate\Http\RedirectResponse;
use JustusTheis\Registry\Models\RegistryEntry;
use JustusTheis\Registry\Services\RegistryTreeBuilder;
use JustusTheis\Registry\Http\Resources\RegistryEntryResource;
use JustusTheis\Registry\Http\Requests\Registry\StoreRegistryKeyRequest;
use JustusTheis\Registry\Http\Requests\Registry\DeleteRegistryKeyRequest;
use JustusTheis\Registry\Http\Requests\Registry\RenameRegistryKeyRequest;
use JustusTheis\Registry\Http\Requests\Registry\UpdateRegistryKeyRequest;

class RegistryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registry Controller
    |--------------------------------------------------------------------------
    |
    | Handles HTTP requests for registry management operations including
    | viewing, creating, updating, deleting, and renaming registry entries
    | with hierarchical tree structure support.
    |
    */

    /**
     * Display the registry index page with tree structure.
     *
     * @param  Request  $request HTTP request instance
     * @return Response Inertia response with registry data
     */
    public function index(Request $request): Response
    {
        // Set the root view specifically for registry routes
        Inertia::setRootView('registry::app');
        
        $entries = RegistryEntryResource::collection(
            RegistryEntry::orderBy('registrable_type')
                ->orderBy('registrable_id')
                ->orderBy('key')
                ->get()
        )->resolve();
        $tree = RegistryTreeBuilder::handle($entries);

        return Inertia::render('Index', [
            'entries'     => $entries,
            'tree'        => $tree,
            'selectedKey' => $request->query('selected') ?? null,
            'expandedKey' => $request->query('expanded') ?? null,
        ]);
    }

    /**
     * Store a newly created registry entry.
     *
     * @param  StoreRegistryKeyRequest $request Validated request data
     * @return RedirectResponse        Redirect to index with created entry selected
     */
    public function store(StoreRegistryKeyRequest $request)
    {
        $data = $request->perform();

        return redirect()->route('registry.index', ['selected' => $data['entry']['key']], 303)
            ->with('success', $data['message']);
    }

    /**
     * Update the specified registry entry.
     *
     * @param  UpdateRegistryKeyRequest $request Validated request data
     * @param  Registry                 $key     Registry instance to update
     * @return RedirectResponse         Redirect to index with success message
     */
    public function update(UpdateRegistryKeyRequest $request, Registry $key): RedirectResponse
    {
        $request->perform($key);

        return redirect()->route('registry.index', [], 303)
            ->with('success', 'Registry entry updated successfully');
    }

    /**
     * Remove the specified registry entry from storage.
     *
     * @param  DeleteRegistryKeyRequest $request Validated request data
     * @param  Registry                 $key     Registry instance to delete
     * @return Response                 Inertia response with updated tree structure
     */
    public function destroy(DeleteRegistryKeyRequest $request, Registry $key)
    {
        $data = $request->perform($key);

        $keyParts = explode('.', $key->getHierarchicalKey());
        $expandToKey = count($keyParts) > 1 ? implode('.', array_slice($keyParts, 0, -1)) : null;

        return redirect()->route('registry.index', $expandToKey ? ['expanded' => $expandToKey] : [], 303)
            ->with('success', $data['message']);
    }

    /**
     * Rename the specified registry key.
     *
     * @param  RenameRegistryKeyRequest $request Validated request data
     * @param  Registry                 $key     Registry instance to rename
     * @return RedirectResponse         Redirect to index with success message
     */
    public function rename(RenameRegistryKeyRequest $request, Registry $key): RedirectResponse
    {
        $request->perform($key);

        return redirect()->route('registry.index', [], 303)
            ->with('success', 'Registry entry renamed successfully');
    }
}

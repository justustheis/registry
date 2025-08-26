<?php

namespace JustusTheis\Registry\Http\Requests\Registry;

use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Registry;
use Illuminate\Foundation\Http\FormRequest;
use JustusTheis\Registry\Http\Rules\UniqueRegistryKey;
use JustusTheis\Registry\Http\Rules\ValidRegistryKeyFormat;

class RenameRegistryKeyRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Rename Registry Key Request
    |--------------------------------------------------------------------------
    |
    | Handles validation and renaming of registry keys with uniqueness
    | checking and optional child key renaming to maintain hierarchical
    | integrity throughout the registry system.
    |
    */
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Check if authorization is disabled
        if (!config('registry.authorization.enabled', true)) {
            return true;
        }

        // Check if we're in a bypass environment
        $bypassEnvs = config('registry.bypass_authorization_envs', []);
        if (in_array(app()->environment(), $bypassEnvs)) {
            return true;
        }

        // Use the configured gate
        $gate = config('registry.authorization.gate', 'access-registry');
        
        // If gate doesn't exist, allow access (graceful degradation)
        if (!Gate::has($gate)) {
            return true;
        }
        
        return Gate::allows($gate);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'new_key' => [
                'required',
                'string',
                'max:255',
                new ValidRegistryKeyFormat(),
                new UniqueRegistryKey($this->route('key')->getKey()),
            ],
            'rename_children' => 'boolean',
        ];
    }

    /**
     * Perform the registry key rename after validation.
     *
     * @param  Registry             $registry The registry instance to rename
     * @return array<string, mixed> The rename result with registry data and count
     */
    public function perform(Registry $registry): array
    {
        $data = $this->validated();

        $registry->rename($data['new_key'], $data['rename_children']);

        return [
            'registry' => $registry,
            'message'  => 'Registry entry renamed successfully',
        ];
    }
}

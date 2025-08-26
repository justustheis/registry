<?php

namespace JustusTheis\Registry\Http\Requests\Registry;

use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Registry;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRegistryKeyRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Delete Registry Key Request
    |--------------------------------------------------------------------------
    |
    | Handles validation and deletion of registry entries.
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
            //
        ];
    }

    /**
     * Perform the registry entry deletion after validation.
     *
     * @param  Registry      $registry The registry instance to delete
     * @return array<string> The deletion message
     */
    public function perform(Registry $registry): array
    {
        $registry->delete();

        return [
            'message' => 'Registry entry deleted successfully',
        ];
    }
}

<?php

namespace JustusTheis\Registry\Http\Requests\Registry;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Registry;
use Illuminate\Foundation\Http\FormRequest;
use JustusTheis\Registry\Enums\RegistryValueType;

class UpdateRegistryKeyRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Update Registry Key Request
    |--------------------------------------------------------------------------
    |
    | Handles validation and updating of existing registry entries, allowing
    | modification of values, types, and encryption settings while preserving
    | the original key structure.
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
            'value'     => 'nullable',
            'type'      => ['nullable', Rule::enum(RegistryValueType::class)],
            'encrypted' => 'boolean',
        ];
    }

    /**
     * Perform the registry entry update after validation.
     *
     * @param  Registry             $registry The registry instance to update
     * @return array<string, mixed> The updated registry data and success message
     */
    public function perform(Registry $registry): array
    {
        $data = $this->validated();

        $registry->value($data['value'])
            ->type($data['type'])
            ->encryption($data['encrypted'])
            ->set();

        return [
            'registry' => $registry,
            'message'  => 'Registry entry updated successfully',
        ];
    }
}

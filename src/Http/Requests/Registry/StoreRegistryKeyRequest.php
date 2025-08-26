<?php

namespace JustusTheis\Registry\Http\Requests\Registry;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use JustusTheis\Registry\Facades\Registry;
use Illuminate\Foundation\Http\FormRequest;
use JustusTheis\Registry\Enums\RegistryValueType;
use JustusTheis\Registry\Http\Rules\UniqueRegistryKey;
use JustusTheis\Registry\Http\Rules\ValidRegistryKeyFormat;

class StoreRegistryKeyRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Store Registry Key Request
    |--------------------------------------------------------------------------
    |
    | Handles validation and creation of new registry entries, processing
    | user input for key-value pairs with optional type specification
    | and encryption settings.
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
            'key' => [
                'required',
                'string',
                'max:255',
                new ValidRegistryKeyFormat(),
                new UniqueRegistryKey(),
            ],
            'value'     => 'nullable',
            'type'      => ['nullable', Rule::enum(RegistryValueType::class)],
            'encrypted' => 'boolean',
        ];
    }

    /**
     * Perform the registry entry creation after validation.
     *
     * @return array<string, mixed> The created entry data and success message
     */
    public function perform(): array
    {
        $data = $this->validated();

        $registry = Registry::key($data['key'])->value($data['value']);
        $data['encrypted'] && $registry->encrypt();
        $data['type'] && $registry->type($data['type']);
        $storedValue = $registry->set();

        return [
            'entry' => [
                'key'       => $data['key'],
                'value'     => $storedValue,
                'type'      => $data['type'],
                'encrypted' => $data['encrypted'] ?? false,
            ],
            'message' => 'Registry entry created successfully',
        ];
    }
}

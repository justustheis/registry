<?php

namespace JustusTheis\Registry\Http\Resources;

use Illuminate\Http\Request;
use JustusTheis\Registry\Registry;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistryEntryResource extends JsonResource
{
    /*
    |--------------------------------------------------------------------------
    | Registry Entry Resource
    |--------------------------------------------------------------------------
    |
    | Transforms RegistryEntry models into the frontend data format,
    | handling hierarchical key building and scope information for
    | the registry tree structure and UI display.
    |
    */

    /**
     * Transform the registry entry into an array.
     *
     * @param  Request $request The incoming HTTP request
     * @return array<string, mixed> The transformed registry entry data
     */
    public function toArray(Request $request): array
    {
        $registry = new Registry();
        if ($this->registrable_type && $this->registrable_id) {
            $scopedModel = $this->registrable_type::find($this->registrable_id);
            if ($scopedModel) {
                $registry = $registry->for($scopedModel);
            }
        }
        $registry = $registry->key($this->key);

        return [
            'id'               => $this->id,
            'key'              => $registry->getHierarchicalKey(),
            'original_key'     => $this->key,
            'value'            => $this->value,
            'type'             => $this->type,
            'encrypted'        => $this->encrypted,
            'updated_at'       => $this->updated_at,
            'registrable_type' => $this->registrable_type,
            'registrable_id'   => $this->registrable_id,
            'is_scoped'        => !is_null($this->registrable_type) && !is_null($this->registrable_id),
        ];
    }
}
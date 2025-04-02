<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Requests\Layer\Api;

use Callcocam\Planogram\Rules\ShelfWidthSpaceValidation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [ 
            'quantity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                new ShelfWidthSpaceValidation($this->route('layer')->id, $this->request->all()),
            ], 
        ];
    }

    // TODO: Add validation for max quantity per layer ex: section width / product width
    protected function maxQuantityPerLayer(string $attribute, int $value, Closure $fail): int
    {
        return 5;
    }
}

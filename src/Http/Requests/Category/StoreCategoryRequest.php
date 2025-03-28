<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Requests\Category;

use Callcocam\Planogram\Enums\CategoryStatus;
use Callcocam\Planogram\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'ulid', 'exists:tenants,id'],
            'user_id' => ['required', 'ulid', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:categories', 'alpha_dash'],
            'status' => ['required', new Enum(CategoryStatus::class)],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRolesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('role_ids_present') === '1' && $this->input('role_ids') === null) {
            $this->merge(['role_ids' => []]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::USERS_ASSIGN_ROLES) ?? false;
    }

    public function rules(): array
    {
        return [
            'role_ids_present' => ['required', 'accepted'],
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
        ];
    }
}

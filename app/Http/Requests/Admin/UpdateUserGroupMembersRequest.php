<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserGroupMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::USER_GROUPS_MANAGE) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
        ];
    }
}

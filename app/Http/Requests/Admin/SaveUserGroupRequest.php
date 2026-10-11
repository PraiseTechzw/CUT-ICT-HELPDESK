<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\UserGroup;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveUserGroupRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');
        if (is_string($name)) {
            $this->merge(['slug' => Str::slug(is_string($slug) && $slug !== '' ? $slug : $name)]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::USER_GROUPS_MANAGE) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $group = $this->route('user_group');
        $groupId = $group instanceof UserGroup ? $group->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('user_groups', 'slug')->ignore($groupId)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

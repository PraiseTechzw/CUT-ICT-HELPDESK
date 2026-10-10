<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Team;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveTeamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');
        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'slug' => is_string($slug) && trim($slug) !== ''
                ? Str::slug(trim($slug))
                : (is_string($name) ? Str::slug(trim($name)) : $slug),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::TEAMS_MANAGE) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $team = $this->route('team');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:100', 'alpha_dash:ascii',
                Rule::unique('teams', 'slug')->ignore($team instanceof Team ? $team->id : null),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

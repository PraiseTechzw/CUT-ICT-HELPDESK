<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Campus;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveCampusRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::CAMPUSES_MANAGE) ?? false;
    }

    public function rules(): array
    {
        $campus = $this->route('campus');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:32', 'alpha_dash:ascii',
                Rule::unique('campuses', 'code')->ignore($campus instanceof Campus ? $campus->id : null),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

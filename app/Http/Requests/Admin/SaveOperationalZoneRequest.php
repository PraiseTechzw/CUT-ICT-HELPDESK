<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\OperationalZone;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveOperationalZoneRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::ZONES_MANAGE) ?? false;
    }

    public function rules(): array
    {
        $zone = $this->route('zone');
        $campusId = $this->input('campus_id');

        return [
            'campus_id' => ['required', 'integer', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:32', 'alpha_dash:ascii',
                Rule::unique('operational_zones', 'code')
                    ->where(fn ($query) => $query->where('campus_id', $campusId))
                    ->ignore($zone instanceof OperationalZone ? $zone->id : null),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

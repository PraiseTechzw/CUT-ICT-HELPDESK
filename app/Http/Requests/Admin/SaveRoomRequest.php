<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Room;
use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveRoomRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper(trim($this->input('code')))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::ROOMS_MANAGE) ?? false;
    }

    public function rules(): array
    {
        $room = $this->route('room');
        $buildingId = $this->input('building_id');

        return [
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:32', 'alpha_dash:ascii',
                Rule::unique('rooms', 'code')
                    ->where(fn ($query) => $query->where('building_id', $buildingId))
                    ->ignore($room instanceof Room ? $room->id : null),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

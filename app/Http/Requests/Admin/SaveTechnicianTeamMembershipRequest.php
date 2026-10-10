<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveTechnicianTeamMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::TEAMS_MANAGE) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'technician_id' => ['required', 'integer', 'exists:users,id'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('technician_id')) {
                return;
            }

            $technician = User::query()->find($this->input('technician_id'));
            if ($technician === null || ! $technician->hasRole(Roles::TECHNICIAN)) {
                $validator->errors()->add('technician_id', 'Choose an account that has the Technician role.');
            }
        }];
    }
}

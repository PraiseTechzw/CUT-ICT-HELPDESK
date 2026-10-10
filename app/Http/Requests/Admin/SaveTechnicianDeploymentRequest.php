<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\TechnicianDeployment;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveTechnicianDeploymentRequest extends FormRequest
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
            'operational_zone_id' => ['required', 'integer', 'exists:operational_zones,id'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
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

            if ($validator->errors()->has('technician_id')
                || $validator->errors()->has('operational_zone_id')
                || $validator->errors()->has('starts_on')
                || $validator->errors()->has('ends_on')) {
                return;
            }

            $deployment = $this->route('deployment');
            $duplicate = TechnicianDeployment::query()
                ->where('technician_id', $this->input('technician_id'))
                ->where('operational_zone_id', $this->input('operational_zone_id'))
                ->whereDate('starts_on', $this->input('starts_on'))
                ->whereDate('ends_on', $this->input('ends_on'))
                ->when($deployment instanceof TechnicianDeployment, fn ($query) => $query->where('id', '!=', $deployment->getKey()))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('ends_on', 'This exact technician, zone, and date period is already recorded.');
            }
        }];
    }
}

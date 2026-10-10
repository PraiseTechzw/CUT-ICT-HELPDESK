<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AuditEventFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::AUDIT_VIEW) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string', 'max:120'],
            'actor' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'through' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $from = $this->input('from');
            $through = $this->input('through');

            if ($from === null && $through === null) {
                return;
            }

            if ($validator->errors()->has('from') || $validator->errors()->has('through')) {
                return;
            }

            if (! is_string($from) || ! is_string($through)) {
                $validator->errors()->add('from', 'Choose both dates to filter audit events by date.');

                return;
            }

            $start = CarbonImmutable::createFromFormat('!Y-m-d', $from, (string) config('app.timezone'));
            $end = CarbonImmutable::createFromFormat('!Y-m-d', $through, (string) config('app.timezone'));

            if ($start === false || $end === false) {
                return;
            }

            if ($start->greaterThan($end)) {
                $validator->errors()->add('through', 'The end date must be on or after the start date.');

                return;
            }

            if (((int) $start->diff($end)->days) + 1 > 366) {
                $validator->errors()->add('through', 'The audit date range cannot exceed 366 inclusive calendar days.');
            }
        }];
    }
}

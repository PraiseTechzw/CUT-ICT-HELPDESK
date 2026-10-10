<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TicketAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::TICKETS_VIEW_ALL) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'through' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('from') || $validator->errors()->has('through')) {
                return;
            }

            $from = $this->input('from');
            $through = $this->input('through');
            if (($from === null) !== ($through === null)) {
                $validator->errors()->add('through', 'Provide both date bounds or leave both blank.');

                return;
            }

            if ($from === null) {
                return;
            }

            $timezone = (string) config('app.timezone', 'UTC');
            $start = CarbonImmutable::createFromFormat('!Y-m-d', $from, $timezone);
            $end = CarbonImmutable::createFromFormat('!Y-m-d', $through, $timezone);

            if ($start->greaterThan($end)) {
                $validator->errors()->add('through', 'The end date must be on or after the start date.');

                return;
            }

            if ($start->diffInDays($end) > 365) {
                $validator->errors()->add('through', 'Choose a date range of no more than 366 inclusive days.');
            }
        }];
    }
}

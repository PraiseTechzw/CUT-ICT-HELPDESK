<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticket;
use App\Support\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        $user = $this->user();

        if (! $ticket instanceof Ticket || $user === null) {
            return false;
        }

        abort_unless($user->can('view', $ticket), 404);

        return $user->can('changeStatus', $ticket);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(TicketStatus::values())],
        ];
    }
}

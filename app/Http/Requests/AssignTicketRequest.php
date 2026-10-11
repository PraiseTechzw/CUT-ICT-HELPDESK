<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        $user = $this->user();

        if (! $ticket instanceof Ticket || $user === null) {
            return false;
        }

        abort_unless($user->can('view', $ticket), 404);

        return $user->can('assign', $ticket);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'technician_id' => ['present', 'nullable', 'integer', Rule::exists('users', 'id')],
            'due_at' => ['nullable', 'date'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        $user = $this->user();

        if (! $ticket instanceof Ticket || $user === null) {
            return false;
        }

        abort_unless($user->can('view', $ticket), 404);

        $visibility = $this->input('visibility');

        return is_string($visibility) && $user->can('comment', [$ticket, $visibility]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'visibility' => ['required', 'string', Rule::in(['public', 'internal'])],
            'body' => ['required', 'string', 'max:10000'],
        ];
    }
}

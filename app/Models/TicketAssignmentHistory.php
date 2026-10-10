<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAssignmentHistory extends Model
{
    protected $fillable = ['ticket_id', 'from_technician_id', 'to_technician_id', 'actor_id'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_technician_id');
    }

    public function toTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_technician_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

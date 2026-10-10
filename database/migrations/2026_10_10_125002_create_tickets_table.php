<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('ticket_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('ticket_priority_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('subject', 200);
            $table->text('description');
            $table->string('status', 40)->default('open');
            $table->timestamps();
            $table->index(['requester_id', 'status']);
            $table->index(['assigned_technician_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

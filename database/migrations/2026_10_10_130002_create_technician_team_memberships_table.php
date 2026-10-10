<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_team_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('team_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->unique(['technician_id', 'team_id', 'starts_on'], 'team_memberships_unique_start');
            $table->index(['technician_id', 'starts_on', 'ends_on'], 'team_memberships_person_period');
            $table->index(['team_id', 'starts_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_team_memberships');
    }
};

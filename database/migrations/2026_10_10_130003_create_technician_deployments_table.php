<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_deployments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('operational_zone_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
            $table->unique(
                ['technician_id', 'operational_zone_id', 'starts_on', 'ends_on'],
                'tech_deployments_exact_period_unique'
            );
            $table->index(['technician_id', 'starts_on', 'ends_on'], 'tech_deployments_person_period');
            $table->index(['operational_zone_id', 'starts_on', 'ends_on'], 'tech_deployments_zone_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_deployments');
    }
};

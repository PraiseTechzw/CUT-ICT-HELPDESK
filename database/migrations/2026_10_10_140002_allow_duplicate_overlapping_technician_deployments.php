<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technician_deployments', function (Blueprint $table): void {
            $table->dropUnique('tech_deployments_exact_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('technician_deployments', function (Blueprint $table): void {
            $table->unique(
                ['technician_id', 'operational_zone_id', 'starts_on', 'ends_on'],
                'tech_deployments_exact_period_unique'
            );
        });
    }
};

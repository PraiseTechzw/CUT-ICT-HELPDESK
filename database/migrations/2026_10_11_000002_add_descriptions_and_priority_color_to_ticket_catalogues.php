<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->string('description', 500)->nullable();
        });

        Schema::table('ticket_priorities', function (Blueprint $table): void {
            $table->string('description', 500)->nullable();
            $table->string('color', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_priorities', function (Blueprint $table): void {
            $table->dropColumn(['description', 'color']);
        });

        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }
};
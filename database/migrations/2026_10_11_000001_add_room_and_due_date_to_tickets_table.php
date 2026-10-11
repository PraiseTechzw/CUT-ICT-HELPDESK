<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('room_id')->nullable()->constrained('rooms')->restrictOnDelete();
            $table->timestamp('due_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropForeign(['room_id']);
            $table->dropIndex(['due_at']);
            $table->dropColumn(['room_id', 'due_at']);
        });
    }
};
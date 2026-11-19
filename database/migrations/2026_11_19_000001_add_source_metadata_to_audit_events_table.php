<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->string('ip_address', 45)->nullable()->after('metadata');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};

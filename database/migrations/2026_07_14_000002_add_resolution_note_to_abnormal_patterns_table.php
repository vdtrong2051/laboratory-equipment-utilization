<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abnormal_patterns', function (Blueprint $table): void {
            $table->text('resolution_note')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('abnormal_patterns', function (Blueprint $table): void {
            $table->dropColumn('resolution_note');
        });
    }
};

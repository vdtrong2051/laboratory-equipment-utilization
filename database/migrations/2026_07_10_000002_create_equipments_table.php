<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table): void {
            $table->id();
            $table->string('equipment_code')->unique();
            $table->string('name');
            $table->string('type')->index();
            $table->string('laboratory')->index();
            $table->string('usage_mode')->index();
            $table->unsignedInteger('allowed_usage_duration_minutes')->nullable();
            $table->string('current_operational_status')->default('OFF')->index();
            $table->string('current_analysis_status')->default('NORMAL')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->decimal('utilization_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};

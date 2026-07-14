<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_metrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->timestamp('period_start')->index();
            $table->timestamp('period_end')->index();
            $table->unsignedInteger('total_booked_minutes')->default(0);
            $table->unsignedInteger('total_powered_minutes')->default(0);
            $table->unsignedInteger('total_active_minutes')->default(0);
            $table->unsignedInteger('total_idle_minutes')->default(0);
            $table->decimal('booking_utilization_rate', 5, 2)->default(0);
            $table->decimal('actual_utilization_rate', 5, 2)->default(0);
            $table->decimal('powered_idle_rate', 5, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->unique(['equipment_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_metrics');
    }
};

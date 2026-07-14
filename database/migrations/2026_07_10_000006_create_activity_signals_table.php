<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_signals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->foreignId('usage_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('signal_type')->index();
            $table->decimal('signal_value', 12, 4)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_active')->nullable()->index();
            $table->string('source')->default('simulated')->index();
            $table->string('gateway_id')->nullable()->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->index();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_signals');
    }
};

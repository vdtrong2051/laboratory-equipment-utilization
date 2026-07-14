<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abnormal_patterns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->foreignId('usage_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_name')->index();
            $table->string('severity')->default('info')->index();
            $table->string('status')->default('open')->index();
            $table->text('message')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamp('detected_at')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abnormal_patterns');
    }
};

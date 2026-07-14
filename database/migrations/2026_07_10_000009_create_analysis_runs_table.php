<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('running')->index();
            $table->string('trigger_source')->default('manual')->index();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('period_start')->index();
            $table->timestamp('period_end')->index();
            $table->unsignedInteger('processed_equipment')->default(0);
            $table->unsignedInteger('operational_status_evaluated')->default(0);
            $table->unsignedInteger('usage_metrics_calculated')->default(0);
            $table->unsignedInteger('rule_sets_evaluated')->default(0);
            $table->unsignedInteger('matched_rules')->default(0);
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_runs');
    }
};

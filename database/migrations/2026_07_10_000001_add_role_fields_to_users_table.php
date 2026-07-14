<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('researcher')->after('password')->index();
            $table->string('department')->nullable()->after('role');
            $table->string('student_code')->nullable()->after('department')->index();
            $table->string('staff_code')->nullable()->after('student_code')->index();
            $table->boolean('is_active')->default(true)->after('staff_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'department', 'student_code', 'staff_code', 'is_active']);
        });
    }
};

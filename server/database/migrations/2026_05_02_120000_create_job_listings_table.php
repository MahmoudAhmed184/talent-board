<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('location_id')->constrained('locations');
            $table->string('title');
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->json('required_skills')->nullable();
            $table->text('qualifications')->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->text('benefits')->nullable();
            $table->string('work_type');
            $table->json('technologies')->nullable();
            $table->string('experience_level');
            $table->timestamp('application_deadline')->nullable();
            $table->string('approval_status')->default('pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();

            $table->index(['employer_user_id', 'approval_status']);
            $table->index(['category_id', 'work_type', 'experience_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};

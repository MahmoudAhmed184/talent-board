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
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('location');
            $table->string('category');
            $table->string('work_type');
            $table->string('experience_level');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('moderation_status')->default('pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->index(['employer_id', 'moderation_status']);
            $table->index(['category', 'work_type', 'experience_level']);
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

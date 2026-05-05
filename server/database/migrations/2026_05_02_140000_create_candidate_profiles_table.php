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
        Schema::create('candidate_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->string('location_text')->nullable();
            $table->string('phone')->nullable();
            $table->json('skills')->nullable();
            $table->unsignedBigInteger('default_resume_id')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('default_resume_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};

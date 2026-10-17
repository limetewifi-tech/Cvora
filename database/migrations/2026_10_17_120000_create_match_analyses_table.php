<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('offer_title', 160);
            $table->string('company', 160)->nullable();
            $table->text('offer_text');
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('result');
            $table->json('ai_suggestions')->nullable();
            $table->string('ai_status', 20)->default('skipped');
            $table->string('ai_error', 180)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'match_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_analyses');
    }
};

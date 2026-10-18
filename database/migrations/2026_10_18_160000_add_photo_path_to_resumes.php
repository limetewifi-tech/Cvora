<?php

use App\Support\ResumeTemplateCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            if (! Schema::hasColumn('resumes', 'photo_path')) {
                $table->string('photo_path')->nullable();
            }
        });

        ResumeTemplateCatalog::sync();
    }

    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};

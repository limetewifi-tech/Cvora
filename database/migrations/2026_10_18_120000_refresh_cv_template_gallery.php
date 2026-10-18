<?php

use App\Support\ResumeTemplateCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ResumeTemplateCatalog::sync();
    }

    public function down(): void
    {
        // Les CV existants restent liés aux mêmes identifiants de modèle.
    }
};

<?php

use App\Support\ResumeTemplateCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ResumeTemplateCatalog::installMissing();
    }

    public function down(): void
    {
        // Les modèles peuvent déjà être liés à des CV. On ne les retire pas.
    }
};

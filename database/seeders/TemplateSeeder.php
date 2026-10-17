<?php

namespace Database\Seeders;

use App\Support\ResumeTemplateCatalog;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        ResumeTemplateCatalog::sync();
    }
}

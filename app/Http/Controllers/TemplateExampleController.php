<?php

namespace App\Http\Controllers;

use App\Models\ResumeTemplate;
use App\Services\Documents\ResumePresenter;
use App\Support\SampleDocument;
use Illuminate\Http\Request;

class TemplateExampleController extends Controller
{
    public function show(ResumeTemplate $template, ResumePresenter $presenter)
    {
        abort_unless($template->is_active, 404);

        return view('public.template-example', [
            'template' => $template,
        ]);
    }

    public function paper(Request $request, ResumeTemplate $template, ResumePresenter $presenter)
    {
        abort_unless($template->is_active, 404);

        return view('resumes.paper', [
            'document' => $presenter->present(SampleDocument::resume($template)),
            'title' => 'Exemple de mise en page — '.$template->name,
            'screen' => true,
            'embedded' => $request->boolean('miniature'),
        ]);
    }
}

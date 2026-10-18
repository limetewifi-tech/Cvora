<?php

namespace App\Services\Documents;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Support\LetterSheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfExporter
{
    public function __construct(private ResumePresenter $presenter) {}

    public function resume(Resume $resume): Response
    {
        $pdf = Pdf::loadView('resumes.paper', [
            'document' => $this->presenter->present($resume),
            'title' => $resume->title,
        ])->setPaper('a4');

        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf->download($this->filename($resume->title, 'pdf'));
    }

    public function letter(CoverLetter $letter): Response
    {
        $pdf = Pdf::loadView('letters.paper', [
            'sheet' => LetterSheet::from($letter),
            'title' => $letter->title,
        ])->setPaper('a4');

        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download($this->filename($letter->title, 'pdf'));
    }

    private function filename(string $title, string $extension): string
    {
        $slug = \Illuminate\Support\Str::slug($title);

        return ($slug !== '' ? $slug : 'document').'.'.$extension;
    }
}

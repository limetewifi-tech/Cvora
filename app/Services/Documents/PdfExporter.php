<?php

namespace App\Services\Documents;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Services\Billing\QuotaService;
use App\Support\LetterSheet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Le PDF reprend la vue d'aperçu. DomPDF reste le moteur : il est livré
 * avec l'application et n'exige pas Chrome sur le serveur. Les photos sont
 * recadrées avant l'insertion, pour que l'image ne soit ni étirée ni absente.
 */
class PdfExporter
{
    public function __construct(private ResumePresenter $presenter) {}

    public function resume(Resume $resume): Response
    {
        $resume->loadMissing('user');
        $pdf = Pdf::loadView('resumes.paper', [
            'document' => $this->presenter->present($resume),
            'title' => $resume->title,
            'watermark' => $resume->user === null || ! app(QuotaService::class)->allowsCleanResumePrint($resume->user),
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
        $slug = Str::slug($title);

        return ($slug !== '' ? $slug : 'document').'.'.$extension;
    }
}

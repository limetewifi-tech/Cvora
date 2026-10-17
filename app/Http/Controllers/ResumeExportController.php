<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Models\Resume;
use App\Services\Billing\QuotaService;
use App\Services\Documents\DocxExporter;
use App\Services\Documents\PdfExporter;
use Illuminate\Http\Request;

class ResumeExportController extends Controller
{
    public function download(Request $request, Resume $resume, string $format, QuotaService $quota, PdfExporter $pdf, DocxExporter $docx)
    {
        $this->authorize('view', $resume);

        try {
            $quota->assertCanExport($request->user(), $format);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception, route('cvs.edit', $resume));
        }

        return $format === 'docx' ? $docx->resume($resume) : $pdf->resume($resume);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Models\CoverLetter;
use App\Models\CoverLetterVersion;
use App\Services\Billing\QuotaService;
use App\Services\Documents\DocxExporter;
use App\Services\Documents\PdfExporter;
use App\Support\LetterLayouts;
use App\Support\LetterSheet;
use App\Support\ResumeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoverLetterController extends Controller
{
    public function index(Request $request)
    {
        return view('letters.index', [
            'letters' => $request->user()->coverLetters()->latest('updated_at')->get(),
            'quota' => app(QuotaService::class),
            'layouts' => LetterLayouts::all(),
        ]);
    }

    public function create(Request $request, QuotaService $quota)
    {
        $layout = (string) $request->query('modele', 'classique');

        return view('letters.edit', $this->editorData($request, new CoverLetter([
            'title' => 'Lettre de motivation',
            'tone' => 'professionnel',
            'length' => 'standard',
            'layout' => LetterLayouts::has($layout) ? $layout : 'classique',
            'body' => '',
            'lock_version' => 0,
        ]), $quota));
    }

    public function store(Request $request, QuotaService $quota)
    {
        $data = $this->validateLetter($request);

        try {
            $quota->assertCanCreateLetter($request->user());
            $this->guardLinks($request, $data);
            $letter = $request->user()->coverLetters()->create($data + ['lock_version' => 0]);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        return redirect()->route('letters.edit', $letter)->with('success', 'Lettre enregistrée.');
    }

    public function edit(Request $request, CoverLetter $letter, QuotaService $quota)
    {
        $this->authorize('update', $letter);

        return view('letters.edit', $this->editorData($request, $letter, $quota));
    }

    public function update(Request $request, CoverLetter $letter)
    {
        $this->authorize('update', $letter);
        $data = $this->validateLetter($request, true);

        if ((int) $data['lock_version'] !== (int) $letter->lock_version) {
            return $this->fail($request, new PlanLimitException('Cette lettre a été modifiée entre-temps. Rechargez la page avant d\'enregistrer.'));
        }

        $this->guardLinks($request, $data);
        $letter->fill($data);
        $letter->lock_version++;
        $letter->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Enregistré', 'lock_version' => $letter->lock_version]);
        }

        return redirect()->route('letters.edit', $letter)->with('success', 'Lettre enregistrée.');
    }

    public function destroy(Request $request, CoverLetter $letter)
    {
        $this->authorize('delete', $letter);
        $request->validate(['confirm' => ['accepted']]);
        $letter->delete();

        return redirect()->route('letters.index')->with('success', 'La lettre a été supprimée.');
    }

    public function snapshot(Request $request, CoverLetter $letter, QuotaService $quota)
    {
        $this->authorize('update', $letter);

        try {
            $quota->assertFeature($request->user(), 'version_history', 'L\'historique complet des versions est inclus dans l\'offre Pro.');
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        $this->storeVersion($letter, 'manual', $request->string('note')->toString() ?: 'Version manuelle');

        return back()->with('success', 'Version de la lettre enregistrée.');
    }

    public function restore(Request $request, CoverLetter $letter, CoverLetterVersion $version)
    {
        $this->authorize('update', $letter);
        abort_unless($version->cover_letter_id === $letter->id, 404);
        $this->storeVersion($letter, 'safety', 'Avant restauration');
        $snapshot = $version->snapshot;
        $letter->fill([
            'title' => $snapshot['title'] ?? $letter->title,
            'company' => $snapshot['company'] ?? null,
            'position' => $snapshot['position'] ?? null,
            'tone' => $snapshot['tone'] ?? $letter->tone,
            'length' => $snapshot['length'] ?? $letter->length,
            'layout' => $snapshot['layout'] ?? $letter->layout,
            'body' => $snapshot['body'] ?? '',
            'lock_version' => $letter->lock_version + 1,
        ])->save();

        return redirect()->route('letters.edit', $letter)->with('success', 'La version de la lettre a été restaurée.');
    }

    public function sample(string $layout)
    {
        abort_unless(LetterLayouts::has($layout), 404);

        return view('letters.paper', [
            'sheet' => LetterSheet::example($layout),
            'title' => 'Exemple de lettre — '.LetterLayouts::all()[$layout]['name'],
            'screen' => true,
            'embedded' => true,
        ]);
    }

    public function preview(Request $request, CoverLetter $letter)
    {
        $this->authorize('view', $letter);

        return view('letters.paper', [
            'sheet' => LetterSheet::from($letter),
            'title' => $letter->title,
            'screen' => true,
            'embedded' => $request->boolean('cadre'),
        ]);
    }

    public function export(Request $request, CoverLetter $letter, string $format, QuotaService $quota, PdfExporter $pdf, DocxExporter $docx)
    {
        $this->authorize('view', $letter);

        try {
            $quota->assertCanExport($request->user(), $format);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception, route('letters.edit', $letter));
        }

        return $format === 'docx' ? $docx->letter($letter) : $pdf->letter($letter);
    }

    public function apply(Request $request, CoverLetter $letter)
    {
        $this->authorize('update', $letter);
        $data = $request->validate(['body' => ['required', 'string', 'max:12000']]);
        $this->storeVersion($letter, 'safety', 'Avant proposition confirmée');
        $letter->forceFill([
            'body' => ResumeContent::clean($data['body'], 12000, true),
            'lock_version' => $letter->lock_version + 1,
        ])->save();

        return redirect()->route('letters.edit', $letter)->with('success', 'La lettre confirmée a été enregistrée. La version précédente est conservée.');
    }

    private function validateLetter(Request $request, bool $updating = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'position' => ['nullable', 'string', 'max:160'],
            'tone' => ['required', 'in:classique,professionnel,direct,debutant'],
            'length' => ['required', 'in:courte,standard,detaillee'],
            'layout' => ['nullable', 'in:'.implode(',', LetterLayouts::keys())],
            'body' => ['nullable', 'string', 'max:12000'],
            'resume_id' => ['nullable', 'integer', Rule::exists('resumes', 'id')->where('user_id', $request->user()->id)],
            'job_offer_id' => ['nullable', 'integer', Rule::exists('job_offers', 'id')->where('user_id', $request->user()->id)],
        ];

        if ($updating) {
            $rules['lock_version'] = ['required', 'integer'];
        }

        $data = $request->validate($rules);
        $data['layout'] = LetterLayouts::has((string) ($data['layout'] ?? '')) ? $data['layout'] : 'classique';

        return $data;
    }

    private function guardLinks(Request $request, array $data): void
    {
        // Les règles exists limitent déjà les liens au propriétaire.
    }

    private function editorData(Request $request, CoverLetter $letter, QuotaService $quota): array
    {
        return [
            'letter' => $letter,
            'resumes' => $request->user()->resumes()->orderBy('title')->get(),
            'offers' => $request->user()->jobOffers()->orderBy('title')->get(),
            'versions' => $letter->exists ? $letter->versions()->latest()->limit(10)->get() : collect(),
            'historyEnabled' => $quota->allows($request->user(), 'version_history'),
            'canExportPdf' => $quota->allows($request->user(), 'export_pdf'),
            'canExportDocx' => $quota->allows($request->user(), 'export_docx'),
        ];
    }

    private function storeVersion(CoverLetter $letter, string $kind, string $note): void
    {
        DB::transaction(function () use ($letter, $kind, $note) {
            $number = (int) $letter->versions()->max('version_number') + 1;
            $letter->versions()->create([
                'user_id' => $letter->user_id,
                'version_number' => $number,
                'kind' => $kind,
                'note' => ResumeContent::clean($note, 160),
                'snapshot' => $letter->only(['title', 'company', 'position', 'tone', 'length', 'layout', 'body']),
            ]);
        });
    }
}

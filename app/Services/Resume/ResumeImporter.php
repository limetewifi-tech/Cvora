<?php

namespace App\Services\Resume;

use App\Models\ResumeImport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Throwable;

class ResumeImporter
{
    public function __construct(
        private ResumeTextParser $parser,
        private OcrExtractor $ocr,
    ) {}

    public function store(User $user, UploadedFile $file): ResumeImport
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();
        $allowed = [
            'pdf' => ['application/pdf'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/octet-stream',
            ],
        ];

        if (! isset($allowed[$extension]) || ! in_array($mime, $allowed[$extension], true)) {
            return $this->failed($user, $file, 'Le fichier doit être un PDF ou un document Word .docx.');
        }

        $max = (int) config('cvora.import.max_kb') * 1024;

        if ($file->getSize() > $max) {
            return $this->failed($user, $file, 'Le fichier dépasse la taille autorisée.');
        }

        $path = $file->storeAs('imports/'.$user->id, Str::uuid()->toString().'.'.$extension, 'local');
        $absolute = Storage::disk('local')->path($path);

        try {
            $text = $extension === 'pdf' ? $this->pdfText($absolute) : $this->docxText($absolute);
        } catch (Throwable) {
            Storage::disk('local')->delete($path);

            return $this->failed($user, $file, 'Impossible de lire ce fichier. Vérifiez qu\'il n\'est pas protégé ou endommagé.');
        }

        $warnings = [];
        $min = (int) config('cvora.import.min_text_length');

        if (mb_strlen(trim($text)) < $min) {
            if ($this->ocr->available()) {
                $text = (string) $this->ocr->extract($absolute);
            }

            if (mb_strlen(trim($text)) < $min) {
                $warnings[] = $extension === 'pdf'
                    ? 'Ce PDF ne contient pas de texte exploitable. Il s\'agit peut-être d\'un document scanné. La reconnaissance optique (OCR) n\'est pas configurée : vous pouvez saisir les informations manuellement.'
                    : 'Peu de texte a été extrait. Vérifiez et complétez les champs avant d\'enregistrer.';
            }
        }

        $parsed = $this->parser->parse($text);

        return $user->imports()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $this->cleanName($file->getClientOriginalName()),
            'mime' => $mime,
            'size' => $file->getSize() ?: 0,
            'status' => $warnings && mb_strlen(trim($text)) < $min ? 'failed' : 'pending_review',
            'extracted_text' => mb_substr($text, 0, 200000),
            'parsed' => $parsed,
            'warnings' => $warnings,
        ]);
    }

    public function forgetFile(ResumeImport $import): void
    {
        Storage::disk($import->disk)->delete($import->path);
        $import->forceFill([
            'path' => '',
            'extracted_text' => null,
        ])->save();
    }

    private function failed(User $user, UploadedFile $file, string $warning): ResumeImport
    {
        return $user->imports()->create([
            'disk' => 'local',
            'path' => '',
            'original_name' => $this->cleanName($file->getClientOriginalName()),
            'mime' => (string) $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'status' => 'failed',
            'warnings' => [$warning],
            'parsed' => ['personal' => [], 'summary' => '', 'content' => [], 'detected' => []],
        ]);
    }

    private function pdfText(string $absolute): string
    {
        $pdf = (new Parser)->parseFile($absolute);

        return trim($pdf->getText());
    }

    private function docxText(string $absolute): string
    {
        $phpWord = IOFactory::load($absolute);
        $lines = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $lines[] = $this->elementText($element);
            }
        }

        return trim(implode("\n", array_filter($lines, fn ($line) => $line !== '')));
    }

    private function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));

        return mb_substr(trim(strip_tags($name)) ?: 'document', 0, 180);
    }

    private function elementText(object $element): string
    {
        if (method_exists($element, 'getElements')) {
            $parts = [];

            foreach ($element->getElements() as $child) {
                $parts[] = $this->elementText($child);
            }

            $joined = trim(implode(' ', array_filter($parts)));

            if ($joined !== '') {
                return $joined;
            }
        }

        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            return is_string($text) ? trim($text) : '';
        }

        return '';
    }
}

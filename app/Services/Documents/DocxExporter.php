<?php

namespace App\Services\Documents;

use App\Models\CoverLetter;
use App\Models\Resume;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocxExporter
{
    public function __construct(private ResumePresenter $presenter) {}

    public function resume(Resume $resume): BinaryFileResponse
    {
        $document = $this->presenter->present($resume);
        $phpWord = $this->document();
        $section = $phpWord->addSection($this->margins());
        $primary = ltrim($document['primary'], '#');

        $section->addText($document['name'], ['bold' => true, 'size' => 22, 'color' => $primary], ['spaceAfter' => 80]);

        if ($document['headline'] !== '') {
            $section->addText($document['headline'], ['italic' => true, 'size' => 12, 'color' => '374151'], ['spaceAfter' => 80]);
        }

        if ($document['contact']) {
            $section->addText(implode('  ·  ', $document['contact']), ['size' => 10, 'color' => '4B5563'], ['spaceAfter' => 200]);
        }

        if ($document['summary'] !== '') {
            $section->addText('Profil', ['bold' => true, 'size' => 13, 'color' => $primary], ['spaceBefore' => 160, 'spaceAfter' => 80]);
            $this->paragraphs($section, $document['summary']);
        }

        foreach ($document['sections'] as $block) {
            $section->addText($block['label'], ['bold' => true, 'size' => 13, 'color' => $primary], ['spaceBefore' => 200, 'spaceAfter' => 80]);

            foreach ($block['items'] as $item) {
                $section->addText($item['primary'], ['bold' => true, 'size' => 11]);
                $meta = trim(implode('  ·  ', array_filter([$item['secondary'], $item['dates']])));

                if ($meta !== '') {
                    $section->addText($meta, ['italic' => true, 'size' => 10, 'color' => '4B5563']);
                }

                if ($item['body'] !== '') {
                    $this->paragraphs($section, $item['body']);
                }
            }
        }

        return $this->download($phpWord, $resume->title);
    }

    public function letter(CoverLetter $letter): BinaryFileResponse
    {
        $phpWord = $this->document();
        $section = $phpWord->addSection($this->margins());
        $section->addText($letter->title, ['bold' => true, 'size' => 16, 'color' => '2563EB'], ['alignment' => Jc::START, 'spaceAfter' => 200]);

        $meta = trim(implode(' — ', array_filter([$letter->position, $letter->company])));

        if ($meta !== '') {
            $section->addText($meta, ['italic' => true, 'size' => 11, 'color' => '374151'], ['spaceAfter' => 200]);
        }

        $this->paragraphs($section, (string) $letter->body);

        return $this->download($phpWord, $letter->title);
    }

    private function document(): PhpWord
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        return $phpWord;
    }

    private function margins(): array
    {
        return [
            'marginTop' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
            'marginRight' => 900,
        ];
    }

    private function paragraphs(object $section, string $text): void
    {
        foreach (preg_split("/\n+/", trim($text)) ?: [] as $paragraph) {
            if (trim($paragraph) !== '') {
                $section->addText(trim($paragraph), ['size' => 11], ['spaceAfter' => 80]);
            }
        }
    }

    private function download(PhpWord $phpWord, string $title): BinaryFileResponse
    {
        $slug = Str::slug($title);
        $filename = ($slug !== '' ? $slug : 'document').'.docx';
        $temp = tempnam(sys_get_temp_dir(), 'cvora');
        IOFactory::createWriter($phpWord, 'Word2007')->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}

<?php

namespace Tests\Feature;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_free_plan_cannot_export(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)
            ->from(route('cvs.edit', $resume))
            ->get(route('cvs.export', [$resume, 'pdf']))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->get(route('cvs.export', [$resume, 'docx']))
            ->assertSessionHas('error');
    }

    public function test_pdf_and_docx_exports_keep_french_content(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);

        $pdf = $this->actingAs($user)->get(route('cvs.export', [$resume, 'pdf']));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $path = tempnam(sys_get_temp_dir(), 'cvora').'.pdf';
        file_put_contents($path, $pdf->getContent());
        $text = (new Parser)->parseFile($path)->getText();
        $this->assertStringContainsString('Amina Kabila', $text);
        $this->assertStringContainsString('Université de Kinshasa', $text);

        $docx = $this->get(route('cvs.export', [$resume, 'docx']));
        $docx->assertOk();
        $file = $docx->baseResponse->getFile()->getPathname();
        $this->assertStringStartsWith('PK', file_get_contents($file));

        $phpWord = IOFactory::load($file);
        $extracted = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText') && is_string($element->getText())) {
                    $extracted .= ' '.$element->getText();
                }
            }
        }

        $this->assertStringContainsString('Amina Kabila', $extracted);
        $this->assertStringContainsString('Université de Kinshasa', $extracted);
    }
}

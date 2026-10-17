<?php

namespace Tests\Feature;

use App\Models\ResumeImport;
use App\Models\ResumeTemplate;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_a_docx_is_parsed_and_saved_only_after_confirmation(): void
    {
        $user = $this->makeUser();
        $file = $this->docx($this->sampleText());

        $this->actingAs($user)->post(route('imports.store'), ['file' => $file])
            ->assertRedirect();

        $import = ResumeImport::query()->first();
        $this->assertNotNull($import);
        $this->assertSame('pending_review', $import->status);
        $this->assertSame('Amina', $import->parsed['personal']['first_name']);
        $this->assertSame(0, $user->resumes()->count());

        $this->get(route('imports.show', $import))
            ->assertOk()
            ->assertSee('Amina')
            ->assertSee('pas une copie visuelle');

        $this->post(route('imports.confirm', $import), [
            'confirmed' => '1',
            'title' => 'CV importé',
            'summary' => 'Analyste à Kinshasa.',
            'personal' => [
                'first_name' => 'Amina',
                'last_name' => 'Kabila',
                'email' => 'amina.kabila@exemple.cd',
            ],
        ])->assertRedirect();

        $resume = $user->resumes()->first();
        $this->assertSame('CV importé', $resume->title);
        $this->assertSame('import', $resume->source);
        $this->assertSame('confirmed', $import->fresh()->status);
        $this->assertSame('', $import->fresh()->path);
    }

    public function test_an_existing_resume_is_not_overwritten_without_confirmation(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);
        $file = $this->docx($this->sampleText());

        $this->actingAs($user)->post(route('imports.store'), ['file' => $file]);
        $import = ResumeImport::query()->firstOrFail();

        $this->post(route('imports.confirm', $import), [
            'confirmed' => '1',
            'title' => 'Écrasement',
            'resume_id' => $resume->id,
            'summary' => 'Nouveau résumé',
        ])->assertSessionHas('error');

        $this->assertSame('CV principal', $resume->fresh()->title);
        $this->assertNotSame('confirmed', $import->fresh()->status);

        $this->post(route('imports.confirm', $import), [
            'confirmed' => '1',
            'overwrite_confirmed' => '1',
            'title' => 'CV mis à jour',
            'resume_id' => $resume->id,
            'summary' => 'Résumé confirmé après importation.',
            'personal' => ['first_name' => 'Amina', 'last_name' => 'Kabila'],
        ])->assertRedirect(route('cvs.edit', $resume));

        $this->assertSame('CV mis à jour', $resume->fresh()->title);
        $this->assertDatabaseHas('resume_versions', [
            'resume_id' => $resume->id,
            'kind' => 'safety',
        ]);
    }

    public function test_a_scanned_pdf_is_reported_without_inventing_fields(): void
    {
        $user = $this->makeUser();
        $file = $this->pdf('<p>CV</p>');

        $this->actingAs($user)->post(route('imports.store'), ['file' => $file])->assertRedirect();

        $import = ResumeImport::query()->firstOrFail();
        $this->assertSame('failed', $import->status);
        $this->assertStringContainsString('scanné', implode(' ', $import->warnings ?? []));

        $this->get(route('imports.show', $import))->assertSee('scanné');
    }

    public function test_a_text_file_is_rejected(): void
    {
        $user = $this->makeUser();
        $file = UploadedFile::fake()->create('notes.txt', 20, 'text/plain');

        $this->actingAs($user)->post(route('imports.store'), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, ResumeImport::query()->count());
    }

    public function test_a_confirmed_import_uses_the_chosen_template(): void
    {
        $user = $this->makeUser();
        $file = $this->docx($this->sampleText());
        $classique = ResumeTemplate::query()->where('slug', 'classique')->firstOrFail();

        $this->actingAs($user)->post(route('imports.store'), ['file' => $file])->assertRedirect();
        $import = ResumeImport::query()->firstOrFail();

        $this->post(route('imports.confirm', $import), [
            'confirmed' => '1',
            'title' => 'CV importé',
            'summary' => 'Analyste à Kinshasa.',
            'template_id' => $classique->id,
            'remember_template' => '1',
            'personal' => [
                'first_name' => 'Amina',
                'last_name' => 'Kabila',
            ],
        ])->assertRedirect();

        $resume = $user->resumes()->first();
        $this->assertSame($classique->id, $resume->template_id);
        $this->assertSame('classique', $user->profile->fresh()->preference('default_template'));
    }

    private function sampleText(): string
    {
        return implode("\n", [
            'Amina Kabila',
            'Analyste financière',
            'amina.kabila@exemple.cd',
            '+243810000000',
            'Kinshasa',
            'Résumé',
            'Analyste à Kinshasa.',
            'Expériences',
            'Analyste — Banque exemple, Kinshasa',
            '2022 — 2024',
            'Suivi des dossiers clients.',
            'Formation',
            'Licence — Université de Kinshasa',
            '2018 — 2021',
            'Compétences',
            'Excel, Analyse',
            'Langues',
            'Français — Courant',
        ]);
    }

    private function docx(string $text): UploadedFile
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        foreach (preg_split("/\n/", $text) as $line) {
            $section->addText($line);
        }

        $path = tempnam(sys_get_temp_dir(), 'cvora').'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return new UploadedFile($path, 'cv-amina.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    private function pdf(string $html): UploadedFile
    {
        $dompdf = new \Dompdf\Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->render();
        $path = tempnam(sys_get_temp_dir(), 'cvora').'.pdf';
        file_put_contents($path, $dompdf->output());

        return new UploadedFile($path, 'scan.pdf', 'application/pdf', null, true);
    }
}

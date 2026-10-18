<?php

namespace Tests\Feature;

use App\Models\ResumeTemplate;
use App\Services\Documents\PdfExporter;
use App\Services\Documents\ResumePresenter;
use App\Support\ResumeContent;
use App\Support\SampleDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;
use ZipArchive;

class ResumeTemplateTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_each_template_has_its_own_structure_and_a_monogram_without_a_photo(): void
    {
        $expected = [
            'classique' => ['layout-classic', 'double-rule'],
            'minimaliste' => ['layout-minimal', 'hairline'],
            'moderne' => ['layout-modern', 'color-bar'],
            'technique' => ['layout-technical', 'skill-row'],
            'experimente' => ['layout-corporate', 'class="side"'],
            'international' => ['layout-elegant', 'class="band"'],
            'etudiant' => ['layout-blue', 'class="job"'],
            'administratif' => ['layout-executive', 'Curriculum vitae'],
        ];

        $this->assertCount(8, $expected);

        foreach ($expected as $slug => $needles) {
            $template = ResumeTemplate::query()->where('slug', $slug)->firstOrFail();
            $html = view('resumes.paper', [
                'document' => app(ResumePresenter::class)->present(SampleDocument::resume($template)),
                'title' => $template->name,
            ])->render();

            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $html, $slug);
            }

            $this->assertStringContainsString('class="monogram ', $html, $slug);
            $this->assertStringContainsString('Initiales', $html, $slug);
            $this->assertStringNotContainsString('Photo de', $html, $slug);
            $this->assertStringContainsString('density-roomy', $html, $slug);
            $this->assertStringContainsString('margin: 14mm 12mm', $html, $slug);
        }

        $screen = view('resumes.paper', [
            'document' => app(ResumePresenter::class)->present(SampleDocument::resume(
                ResumeTemplate::query()->where('slug', 'classique')->firstOrFail()
            )),
            'title' => 'Classique',
            'screen' => true,
            'embedded' => true,
        ])->render();
        $this->assertStringContainsString('width: 210mm', $screen);
        $this->assertStringContainsString('min-height: 297mm', $screen);
    }

    public function test_a_photo_is_cropped_for_the_template_and_survives_a_template_change(): void
    {
        Storage::fake('local');
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);
        $corporate = ResumeTemplate::query()->where('slug', 'experimente')->firstOrFail();

        $this->actingAs($user)->post(route('cvs.photo', $resume), [
            'photo' => UploadedFile::fake()->image('portrait.png', 900, 500),
        ])->assertRedirect();

        $resume->refresh();
        $this->assertNotNull($resume->photo_path);
        Storage::disk('local')->assertExists($resume->photo_path);
        $this->assertSame(1, $user->resumes()->count());

        $square = $this->actingAs($user)->get(route('cvs.preview', $resume))->assertOk();
        $this->assertStringContainsString('data:image/jpeg;base64,', $square->getContent());
        $modernSize = $this->imageSize($square->getContent());
        $this->assertSame($modernSize[0], $modernSize[1]);

        $this->put(route('cvs.update', $resume), $this->resumeForm($resume, [
            'template_id' => $corporate->id,
        ]))->assertRedirect();

        $resume->refresh();
        $this->assertSame($corporate->id, $resume->template_id);
        $this->assertNotNull($resume->photo_path);
        $this->assertSame('Banque exemple', $resume->content['experiences'][0]['company']);
        $this->assertSame('Analyste à Kinshasa.', $resume->summary);

        $portrait = $this->get(route('cvs.preview', $resume))->assertOk()->assertSee('layout-corporate', false);
        $size = $this->imageSize($portrait->getContent());
        $this->assertGreaterThan($size[0], $size[1]);
        $this->assertStringContainsString('Banque exemple', $portrait->getContent());

        $pdf = app(PdfExporter::class)->resume($resume)->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringContainsString('/Image', $pdf);
        $text = (new Parser)->parseContent($pdf)->getText();
        $this->assertStringContainsString('Amina Kabila', $text);
        $this->assertStringContainsString('Banque exemple', $text);

        $docx = $this->get(route('cvs.export', [$resume, 'docx']));
        $docx->assertOk();
        $zip = new ZipArchive;
        $zip->open($docx->baseResponse->getFile()->getPathname());
        $media = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            if (str_starts_with((string) $zip->getNameIndex($index), 'word/media/')) {
                $media = true;
            }
        }

        $zip->close();
        $this->assertTrue($media);
    }

    public function test_removing_the_photo_restores_the_initials_and_a_stranger_cannot_upload(): void
    {
        Storage::fake('local');
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);
        $this->actingAs($user)->post(route('cvs.photo', $resume), [
            'photo' => UploadedFile::fake()->image('face.jpg', 400, 400),
        ])->assertRedirect();
        $path = $resume->fresh()->photo_path;

        $stranger = $this->makeUser('premium');
        $this->actingAs($stranger)->post(route('cvs.photo', $resume), [
            'photo' => UploadedFile::fake()->image('autre.jpg', 400, 400),
        ])->assertNotFound();
        $this->assertSame($path, $resume->fresh()->photo_path);

        $this->actingAs($user)->delete(route('cvs.photo.destroy', $resume))->assertRedirect();
        $this->assertNull($resume->fresh()->photo_path);
        Storage::disk('local')->assertMissing($path);

        $this->get(route('cvs.preview', $resume))
            ->assertOk()
            ->assertSee('class="monogram photo-square"', false)
            ->assertSee('Initiales AK', false)
            ->assertDontSee('Photo de Amina Kabila', false);
    }

    public function test_a_long_resume_flows_onto_another_page_without_dropping_sections(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);
        $content = $resume->content;
        $content['experiences'] = [];
        $content['projects'] = [];

        for ($index = 1; $index <= 8; $index++) {
            $content['experiences'][] = [
                'title' => 'Mission '.$index,
                'company' => 'Organisation '.$index,
                'location' => 'Kinshasa',
                'start_date' => '201'.$index,
                'end_date' => '202'.$index,
                'description' => str_repeat('Suivi des dossiers, des délais et des indicateurs de cette mission. ', 18),
            ];
        }

        $resume->forceFill([
            'summary' => str_repeat('Parcours construit à Kinshasa, avec des faits vérifiables. ', 8),
            'content' => ResumeContent::normalizeContent($content),
        ])->save();

        $presented = app(ResumePresenter::class)->present($resume->fresh());
        $this->assertSame('compact', $presented['density']);
        $this->assertStringContainsString(
            'density-compact',
            view('resumes.paper', ['document' => $presented, 'title' => 'Long'])->render(),
        );

        $pdf = app(PdfExporter::class)->resume($resume)->getContent();
        $parsed = (new Parser)->parseContent($pdf);

        $this->assertGreaterThan(1, count($parsed->getPages()));
        $this->assertStringContainsString('Mission 1', $parsed->getText());
        $this->assertStringContainsString('Mission 8', $parsed->getText());
        $this->assertStringContainsString('Université de Kinshasa', $parsed->getText());
    }

    public function test_an_unreadable_or_tiny_photo_is_refused(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->post(route('cvs.photo', $resume), [
            'photo' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
        ])->assertSessionHasErrors('photo');

        $this->post(route('cvs.photo', $resume), [
            'photo' => UploadedFile::fake()->image('tiny.jpg', 20, 20),
        ])->assertSessionHasErrors('photo');

        $this->assertNull($resume->fresh()->photo_path);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function imageSize(string $html): array
    {
        $this->assertSame(1, preg_match('/data:image\/(?:jpeg|png);base64,([^"]+)/', $html, $matches));
        $size = getimagesizefromstring(base64_decode($matches[1]));
        $this->assertIsArray($size);

        return [$size[0], $size[1]];
    }
}

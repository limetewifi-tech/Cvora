<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\ResumeTemplate;
use App\Services\Billing\QuotaService;
use App\Services\Documents\DocxExporter;
use App\Services\Documents\PdfExporter;
use App\Services\Documents\ResumePresenter;
use App\Support\LetterLayouts;
use App\Support\ResumeTemplateCatalog;
use App\Support\SampleDocument;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class GalleryCatalogTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_each_profession_has_ten_distinct_resume_and_letter_models(): void
    {
        $definitions = ResumeTemplateCatalog::definitions();
        $this->assertCount(150, $definitions);
        $this->assertSame(150, ResumeTemplate::query()->count());

        $layouts = [];

        foreach (ResumeTemplateCatalog::sectors() as $sector => $label) {
            $rows = array_values(array_filter($definitions, fn (array $row) => $row['sector'] === $sector));
            $this->assertCount(10, $rows, $label);
            $styles = array_column($rows, 'layout');
            $this->assertCount(10, array_unique($styles), $label);
            $layouts = array_merge($layouts, $styles);
        }

        $this->assertEqualsCanonicalizing([
            'classic', 'minimal', 'modern', 'technical', 'corporate', 'elegant', 'blue', 'executive', 'rail', 'banner',
        ], array_values(array_unique($layouts)));

        foreach ([
            'moderne' => 'Moderne',
            'classique' => 'Classique',
            'minimaliste' => 'Minimaliste',
            'technique' => 'Technique',
            'experimente' => 'Corporate',
            'international' => 'Élégant',
            'etudiant' => 'Bleu professionnel',
            'administratif' => 'Exécutif',
        ] as $slug => $name) {
            $this->assertSame($name, ResumeTemplate::query()->where('slug', $slug)->value('name'));
        }

        $letters = LetterLayouts::all();
        $this->assertCount(100, $letters);

        foreach (LetterLayouts::categories() as $category => $label) {
            $rows = array_filter($letters, fn (array $row) => $row['category'] === $category);
            $this->assertCount(10, $rows, $label);
            $this->assertCount(10, array_unique(array_column($rows, 'family')), $label);
        }
    }

    public function test_gallery_thumbnails_render_the_model_and_keep_a_local_fallback(): void
    {
        $gallery = $this->get(route('templates.index'))->assertOk();
        $html = $gallery->getContent();

        $this->assertSame(150, substr_count($html, 'data-category='));
        $this->assertSame(10, substr_count($html, 'data-sector="sante"'));
        $gallery->assertSee('images/cv-apercu.svg', false)
            ->assertSee('iframe title="" data-src=', false)
            ->assertSee('miniature=1', false)
            ->assertSee('Voir le modèle')
            ->assertSee('Utiliser ce modèle')
            ->assertSee('Créer un CV avec l\'IA', false)
            ->assertDontSee('iframe title="" src=', false);

        $letters = $this->get(route('templates.letters'))->assertOk();
        $letterHtml = $letters->getContent();
        $this->assertSame(100, substr_count($letterHtml, 'data-letter-category='));
        $this->assertSame(10, substr_count($letterHtml, 'data-letter-category="informatique"'));
        $letters->assertSee('images/cv-apercu.svg', false)
            ->assertSee('Créer une lettre avec l\'IA', false)
            ->assertSee('Voir le modèle');

        $banner = ResumeTemplate::query()->where('slug', 'sante-banner')->firstOrFail();
        $rail = ResumeTemplate::query()->where('slug', 'sante-rail')->firstOrFail();
        $bannerHtml = $this->paper($banner);
        $railHtml = $this->paper($rail);

        $this->assertStringContainsString('layout-banner', $bannerHtml);
        $this->assertStringContainsString('gold-line', $bannerHtml);
        $this->assertStringContainsString('Aline', $bannerHtml);
        $this->assertStringNotContainsString('class="cvora-mark', $bannerHtml);
        $this->assertStringNotContainsString('edos-services', $bannerHtml);
        $this->assertStringContainsString('layout-rail', $railHtml);
        $this->assertLessThan(strpos($railHtml, 'class="side"'), strpos($railHtml, 'class="main"'));

        $corporate = $this->paper(ResumeTemplate::query()->where('slug', 'experimente')->firstOrFail());
        $this->assertLessThan(strpos($corporate, 'class="main"'), strpos($corporate, 'class="side"'));

        $this->assertFileExists(public_path('images/cv-apercu.svg'));
        $this->assertFileExists(public_path('images/edos-services.png'));
        $this->assertNotFalse(getimagesize(public_path('images/edos-services.png')));

        $this->get(route('templates.letters.paper', 'sante-colonne'))
            ->assertOk()
            ->assertSee('family-colonne', false)
            ->assertSee('Le texte de votre lettre apparaît ici.')
            ->assertDontSee('cvora-mark', false)
            ->assertDontSee('edos-services', false);
    }

    public function test_selecting_a_model_creates_a_resume_with_that_layout(): void
    {
        $user = $this->makeUser();
        $template = ResumeTemplate::query()->where('slug', 'sante-banner')->firstOrFail();

        $this->actingAs($user)->get(route('cvs.create', ['modele' => 'sante-banner']))
            ->assertOk()
            ->assertSee('data-sector-picker', false)
            ->assertSeeInOrder(['Bandeau · Santé', 'Sélectionné']);

        $this->post(route('cvs.store'), [
            'title' => 'CV santé',
            'template_id' => $template->id,
        ])->assertRedirect();

        $resume = $user->resumes()->first();
        $this->assertNotNull($resume);
        $this->assertSame($template->id, $resume->template_id);

        $this->get(route('cvs.preview', $resume))
            ->assertOk()
            ->assertSee('layout-banner', false)
            ->assertSee('gold-line', false)
            ->assertSee('Amina');
    }

    public function test_selecting_a_letter_model_saves_that_layout(): void
    {
        $user = $this->makeUser('premium');
        $body = 'Madame, Monsieur, je présente ma candidature pour le poste d\'infirmière.';

        $this->actingAs($user)->get(route('letters.create', ['modele' => 'sante-colonne']))
            ->assertOk()
            ->assertSee('value="sante-colonne"', false);

        $this->post(route('letters.store'), [
            'title' => 'Lettre santé',
            'company' => 'Centre de santé',
            'position' => 'Infirmière',
            'tone' => 'professionnel',
            'length' => 'standard',
            'layout' => 'sante-colonne',
            'body' => $body,
        ])->assertRedirect();

        $letter = $user->coverLetters()->first();
        $this->assertNotNull($letter);
        $this->assertSame('sante-colonne', $letter->layout);

        $this->get(route('letters.preview', $letter))
            ->assertOk()
            ->assertSee('layout-sante-colonne', false)
            ->assertSee('family-colonne', false)
            ->assertSee($body)
            ->assertSee('Centre de santé')
            ->assertDontSee('cvora-mark', false);

        $pdf = $this->get(route('letters.export', [$letter, 'pdf']));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $path = tempnam(sys_get_temp_dir(), 'cvora-letter').'.pdf';
        file_put_contents($path, $pdf->getContent());
        $this->assertStringContainsString('Centre de santé', (new Parser)->parseFile($path)->getText());

        $docx = $this->get(route('letters.export', [$letter, 'docx']));
        $docx->assertOk();
        $this->assertStringStartsWith('PK', file_get_contents($docx->baseResponse->getFile()->getPathname()));
    }

    public function test_ai_creation_opens_the_assistant_without_writing_the_document(): void
    {
        $user = $this->makeUser();
        $template = ResumeTemplate::query()->where('slug', 'sante-banner')->firstOrFail();

        $this->actingAs($user)->get(route('cvs.create', ['modele' => 'sante-banner', 'ia' => 1]))
            ->assertOk()
            ->assertSee('name="ia" value="1"', false)
            ->assertSee('Une proposition n\'est appliquée que si vous la validez.', false);

        $response = $this->post(route('cvs.store'), [
            'title' => 'CV avec assistant',
            'template_id' => $template->id,
            'ia' => '1',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('assistant=1', (string) $response->headers->get('Location'));

        $resume = $user->resumes()->first();
        $this->assertSame('Analyste à Kinshasa.', $resume->summary);

        $this->get(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 1, 'assistant' => 1]))
            ->assertOk()
            ->assertSee('id="assistant-cv"', false)
            ->assertSee('Ouvrir l\'assistant', false);

        $premium = $this->makeUser('premium');
        $this->actingAs($premium)->get(route('letters.create', ['modele' => 'commerce-centre', 'ia' => 1]))
            ->assertOk()
            ->assertSee('id="lettre-ia"', false)
            ->assertSee('value="commerce-centre"', false)
            ->assertSee('Proposer une lettre avec l\'IA', false)
            ->assertSee('Offre d\'emploi', false);

        $this->assertSame(0, $premium->coverLetters()->count());
    }

    public function test_a_clean_resume_print_requires_an_active_eligible_subscription(): void
    {
        $free = $this->makeUser();
        $resume = $this->makeResume($free);

        $this->assertFalse(app(QuotaService::class)->allowsCleanResumePrint($free));
        $this->assertFalse((bool) $free->subscription->plan->features['export_pdf']);

        $this->actingAs($free)->get(route('cvs.preview', $resume))
            ->assertOk()
            ->assertSee('class="cvora-mark cvora-mark-a"', false)
            ->assertSee('>CVora<', false);

        $this->from(route('cvs.edit', $resume))
            ->get(route('cvs.export', [$resume, 'pdf']))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->get(route('cvs.export', [$resume, 'docx']))
            ->assertRedirect()
            ->assertSessionHas('error');

        $freePdf = app(PdfExporter::class)->resume($resume)->getContent();
        $this->assertStringStartsWith('%PDF', $freePdf);
        $freePdfPath = tempnam(sys_get_temp_dir(), 'cvora-free').'.pdf';
        file_put_contents($freePdfPath, $freePdf);
        $this->assertStringContainsString('CVora', (new Parser)->parseFile($freePdfPath)->getText());
        $this->assertStringContainsString('CVora', $this->docxText(app(DocxExporter::class)->resume($resume)->getFile()->getPathname()));

        $premium = $this->makeUser('premium');
        $clean = $this->makeResume($premium);

        $this->actingAs($premium)->get(route('cvs.preview', $clean))
            ->assertOk()
            ->assertDontSee('class="cvora-mark', false);

        $pdf = $this->get(route('cvs.export', [$clean, 'pdf']));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $pdfPath = tempnam(sys_get_temp_dir(), 'cvora-premium').'.pdf';
        file_put_contents($pdfPath, $pdf->getContent());
        $this->assertStringNotContainsString('CVora', (new Parser)->parseFile($pdfPath)->getText());

        $docx = $this->get(route('cvs.export', [$clean, 'docx']));
        $docx->assertOk();
        $this->assertStringStartsWith('PK', file_get_contents($docx->baseResponse->getFile()->getPathname()));
        $this->assertStringNotContainsString('CVora', $this->docxText($docx->baseResponse->getFile()->getPathname()));

        $premium->subscription->forceFill(['status' => SubscriptionStatus::Expired])->save();
        $this->get(route('cvs.preview', $clean))
            ->assertOk()
            ->assertSee('class="cvora-mark', false);

        $letter = $free->coverLetters()->create([
            'title' => 'Lettre sans filigrane',
            'company' => 'Organisation exemple',
            'position' => 'Poste exemple',
            'tone' => 'professionnel',
            'length' => 'standard',
            'layout' => 'classique',
            'body' => 'Madame, Monsieur, je présente ma candidature.',
        ]);
        $this->actingAs($free)->get(route('letters.preview', $letter))
            ->assertOk()
            ->assertDontSee('cvora-mark', false);
    }

    public function test_the_footer_shows_the_edos_logo_once_and_the_page_can_shrink(): void
    {
        $home = $this->get(route('home'))->assertOk();
        $html = $home->getContent();

        $home->assertSee('Design by EDOS Services', false)
            ->assertSee('images/edos-services.png', false)
            ->assertSee('alt="EDOS Services"', false)
            ->assertSee('width=device-width, initial-scale=1', false);
        $this->assertSame(1, substr_count($html, 'edos-services.png'));

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('overflow-x: clip', $css);
        $this->assertStringContainsString('.cvora-edos', $css);
        $this->assertStringContainsString('max-width: min(200px, 100%)', $css);

        $paper = $this->paper(ResumeTemplate::query()->where('slug', 'moderne')->firstOrFail());
        $this->assertStringNotContainsString('edos-services', $paper);
    }

    private function paper(ResumeTemplate $template): string
    {
        return view('resumes.paper', [
            'document' => app(ResumePresenter::class)->present(SampleDocument::resume($template)),
            'title' => $template->name,
        ])->render();
    }

    private function docxText(string $path): string
    {
        $extracted = '';

        foreach (IOFactory::load($path)->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText') && is_string($element->getText())) {
                    $extracted .= ' '.$element->getText();
                }
            }

            foreach ($section->getHeaders() as $header) {
                foreach ($header->getElements() as $element) {
                    if (method_exists($element, 'getText') && is_string($element->getText())) {
                        $extracted .= ' '.$element->getText();
                    }
                }
            }
        }

        return $extracted;
    }
}

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ResumeImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class PrivateFileTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_an_imported_file_stays_on_the_private_disk(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $admin = $this->makeUser('pro', ['role' => UserRole::Admin]);

        $this->actingAs($owner)->post(route('imports.store'), [
            'file' => $this->docx(),
        ])->assertRedirect();

        $import = ResumeImport::query()->firstOrFail();
        $this->assertSame('local', $import->disk);
        $this->assertTrue(Storage::disk('local')->exists($import->path));
        $this->assertFileDoesNotExist(public_path($import->path));
        $this->assertStringStartsWith('imports/'.$owner->id.'/', $import->path);

        $this->get(route('imports.file', $import))->assertOk();
        $this->actingAs($other)->get(route('imports.file', $import))->assertNotFound();
        $this->actingAs($admin)->get(route('imports.file', $import))->assertNotFound();

        auth()->logout();
        $this->get(route('imports.file', $import))->assertRedirect(route('login'));
    }

    private function docx(): UploadedFile
    {
        $phpWord = new PhpWord;
        $phpWord->addSection()->addText('Amina Kabila, document privé.');
        $path = tempnam(sys_get_temp_dir(), 'cvora').'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return new UploadedFile($path, 'prive.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }
}

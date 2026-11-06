<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Group;
use App\Models\Type;
use App\Models\Work;
use App\Services\WorkFiles;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkFilesTest extends TestCase
{
    public function test_work_page_templates_compile_to_valid_php(): void
    {
        $compiler = app('blade.compiler');
        foreach (['admin.section', 'admin.work-editor', 'admin.layout'] as $view) {
            $path = view()->getFinder()->find($view);
            $compiler->compile($path);
            $tokens = token_get_all(file_get_contents($compiler->getCompiledPath($path)), TOKEN_PARSE);
            $this->assertNotEmpty($tokens, $view);
        }
    }

    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        // The global view composer queries MySQL during boot. These tests use
        // only an isolated in-memory database and do not render that layout.
        $app->bindMethod([\App\Providers\AppServiceProvider::class, 'boot'], fn () => null);
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'work_test', 'database.connections.work_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('work_test');
        Storage::fake('s3');
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('work_title')->nullable();
            $table->timestamps();
        });
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->timestamps();
        });
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->text('type_name');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->text('file_url')->nullable();
            $table->text('part_title')->nullable();
            $table->text('part_cover_url')->nullable();
            $table->integer('position')->nullable();
            $table->integer('media_length')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('work_id');
            $table->timestamps();
        });
        $group = Group::create(['group_name' => 'Type de fichier']);
        foreach (['Image', 'Document', 'Audio'] as $name) {
            Type::create(['group_id' => $group->id, 'type_name' => ['fr' => $name, 'en' => $name.' EN']]);
        }
    }

    public function test_append_preserves_titles_covers_order_and_unique_storage_paths(): void
    {
        $work = Work::create(['work_title' => 'Example']);
        $work->files()->create(['position' => 4]);
        $request = Request::create('/', 'POST', ['part_titles' => ['Chapter one', ''], 'files_names' => ['Custom name']]);
        $request->files->set('files_urls', [UploadedFile::fake()->image('same.jpg'), UploadedFile::fake()->image('same.jpg')]);
        $request->files->set('part_covers', [UploadedFile::fake()->image('cover.png')]);
        $stored = [];
        app(WorkFiles::class)->append($work, $request, $stored);
        $files = $work->files()->where('position', '>', 4)->orderBy('position')->get();
        $this->assertSame([5, 6], $files->pluck('position')->all());
        $this->assertSame('Chapter one', $files[0]->part_title);
        $this->assertSame('Custom name', $files[0]->file_name);
        $this->assertNotNull($files[0]->part_cover_url);
        $this->assertNull($files[1]->part_cover_url);
        $this->assertNotSame($files[0]->file_url, $files[1]->file_url);
        foreach ($stored as $path) {
            Storage::disk('s3')->assertExists($path);
        }
        $this->assertSame('Image EN', $files[0]->type->getTranslation('type_name', 'en'));
    }

    public function test_file_edit_is_scoped_to_its_work(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $other = Work::create([]);
        $file = $other->files()->create(['part_title' => 'Original']);
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", ['part_title' => 'Changed'])->assertNotFound();
        $this->assertSame('Original', $file->fresh()->part_title);
    }

    public function test_reorder_requires_exact_membership_and_persists_positions(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $a = $work->files()->create(['position' => 1]);
        $b = $work->files()->create(['position' => 2]);
        $this->postJson("/admin/work/{$work->id}/files/order", ['ids' => [$a->id]])->assertStatus(422);
        $this->postJson("/admin/work/{$work->id}/files/order", ['ids' => [$b->id, $a->id]])->assertOk();
        $this->assertSame(1, $b->fresh()->position);
        $this->assertSame(2, $a->fresh()->position);
    }

    public function test_reorder_accepts_string_ids_from_multipart_form_data(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $a = $work->files()->create(['position' => 1]);
        $b = $work->files()->create(['position' => 2]);
        $this->post("/admin/work/{$work->id}/files/order", ['ids' => [(string) $b->id, (string) $a->id]], ['Accept' => 'application/json'])->assertOk();
        $this->assertSame(1, $b->fresh()->position);
        $this->assertSame(2, $a->fresh()->position);
    }

    public function test_file_duration_converts_hms_to_seconds_and_rejects_invalid_time(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $file = $work->files()->create([]);
        $url = "/admin/work/{$work->id}/files/{$file->id}";
        $this->postJson($url, ['media_length' => '01:02:03'])->assertOk();
        $this->assertSame(3723, $file->fresh()->media_length);
        $this->postJson($url, ['media_length' => '00:60:00'])->assertUnprocessable()->assertJsonValidationErrors('media_length');
        $this->assertSame(3723, $file->fresh()->media_length);
    }

    public function test_replacement_changes_type_url_and_removes_old_file_and_cover(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $old = "images/works/{$work->id}/photo.jpg";
        $cover = "images/works/{$work->id}/covers/old.jpg";
        Storage::disk('s3')->put($old, 'old');
        Storage::disk('s3')->put($cover, 'cover');
        $image = Type::where('type_name->fr', 'Image')->first();
        $file = $work->files()->create(['position' => 3, 'part_title' => 'Part one', 'type_id' => $image->id,
            'file_url' => Storage::disk('s3')->url($old), 'part_cover_url' => Storage::disk('s3')->url($cover)]);
        $pdf = UploadedFile::fake()->createWithContent('document.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", ['replacement' => $pdf])->assertOk();
        $file->refresh();
        $this->assertSame('Document', $file->type->getTranslation('type_name', 'fr'));
        $this->assertStringContainsString('/documents/works/', $file->file_url);
        $this->assertSame('document.pdf', $file->file_name);
        $this->assertSame(3, $file->position);
        $this->assertSame('Part one', $file->part_title);
        $this->assertNull($file->part_cover_url);
        Storage::disk('s3')->assertMissing($old);
        Storage::disk('s3')->assertMissing($cover);
        $url = $file->file_url;
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", ['replacement' => UploadedFile::fake()->image('new.jpg')])->assertOk();
        $this->assertSame('Image', $file->fresh()->type->getTranslation('type_name', 'fr'));
        Storage::disk('s3')->assertMissing(app(WorkFiles::class)->pathForUrl($url));
    }

    public function test_document_preview_returns_only_requested_bytes_as_json_and_is_work_scoped(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $other = Work::create([]);
        $path = "documents/works/{$work->id}/document.pdf";
        Storage::disk('s3')->put($path, str_repeat('abcdef', 20000));
        $file = $work->files()->create(['file_url' => Storage::disk('s3')->url($path)]);
        $response = $this->postJson("/admin/work/{$work->id}/files/{$file->id}/preview", ['offset' => 6, 'length' => 12]);
        $response->assertOk()->assertHeader('Content-Type', 'application/json')->assertJsonPath('size', 120000);
        $this->assertSame('abcdefabcdef', base64_decode($response->json('data')));
        $this->postJson("/admin/work/{$other->id}/files/{$file->id}/preview", [])->assertNotFound();
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}/preview", ['length' => 99999999])->assertUnprocessable();
    }

    public function test_encoder_can_manage_files_using_encoder_routes_but_cannot_use_admin_routes(): void
    {
        $encoder = new \App\Models\User;
        $encoder->id = 1;
        $encoder->setRelation('roles', collect([new \App\Models\Role(['role_name' => 'Encodeur'])]));
        $this->actingAs($encoder);
        $work = Work::create([]);
        $base = "/encoder/work/{$work->id}/files";
        $this->postJson($base, ['files_urls' => [UploadedFile::fake()->image('photo.jpg')]])->assertOk();
        $file = $work->files()->first();
        $this->postJson($base.'/order', ['ids' => [(string) $file->id]])->assertOk();
        $this->postJson($base.'/'.$file->id, ['media_length' => '00:01:30', 'part_title' => 'Updated'])->assertOk();
        $this->assertSame(90, $file->fresh()->media_length);
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", ['part_title' => 'Forbidden'])->assertForbidden();
        $this->deleteJson($base.'/'.$file->id)->assertOk();
        $this->assertNull($file->fresh());
    }

    public function test_docx_preview_returns_a_first_page_excerpt_without_document_markup(): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'work-preview-');
        try {
            $zip = new \ZipArchive;
            $zip->open($temporary, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $zip->addFromString('word/document.xml', '<w:document><w:p><w:r><w:t>First page &amp; title</w:t></w:r></w:p><w:br w:type="page"/><w:p><w:r><w:t>Second page</w:t></w:r></w:p></w:document>');
            $zip->close();
            $work = Work::create([]);
            $path = "documents/works/{$work->id}/document.docx";
            Storage::disk('s3')->put($path, file_get_contents($temporary));
            $text = app(\App\Services\WorkDocumentPreview::class)->excerpt(Storage::disk('s3')->url($path));
            $this->assertStringContainsString('First page & title', $text);
            $this->assertStringNotContainsString('Second page', $text);
            $this->assertStringNotContainsString('<w:', $text);
        } finally {
            unlink($temporary);
        }
    }

    public function test_delete_removes_both_storage_objects_and_database_row(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $path = "images/works/{$work->id}/photo.jpg";
        $cover = "images/works/{$work->id}/covers/cover.jpg";
        Storage::disk('s3')->put($path, 'photo');
        Storage::disk('s3')->put($cover, 'cover');
        $file = $work->files()->create(['file_url' => Storage::disk('s3')->url($path), 'part_cover_url' => Storage::disk('s3')->url($cover)]);
        $this->deleteJson("/admin/work/{$work->id}/files/{$file->id}")->assertOk();
        $this->assertNull(File::find($file->id));
        Storage::disk('s3')->assertMissing($path);
        Storage::disk('s3')->assertMissing($cover);
    }

    public function test_upload_rejects_executable_and_missing_files(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $this->postJson("/admin/work/{$work->id}/files", [])->assertUnprocessable()->assertJsonValidationErrors('files_urls');
        $this->postJson("/admin/work/{$work->id}/files", ['files_urls' => [UploadedFile::fake()->create('bad.php', 1, 'application/x-php')]])->assertUnprocessable();
        $this->assertSame(0, $work->files()->count());
    }

    public function test_cover_replacement_deletes_previous_cover(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $old = "images/works/{$work->id}/covers/old.jpg";
        Storage::disk('s3')->put($old, 'old');
        $file = $work->files()->create(['part_cover_url' => Storage::disk('s3')->url($old)]);
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", ['part_title' => 'New title', 'cover' => UploadedFile::fake()->image('new.jpg')])->assertOk();
        $this->assertSame('New title', $file->fresh()->part_title);
        $this->assertNotSame(Storage::disk('s3')->url($old), $file->fresh()->part_cover_url);
        Storage::disk('s3')->assertMissing($old);
    }

    public function test_acl_disabled_bucket_retries_upload_with_bucket_owner_control(): void
    {
        $upload = UploadedFile::fake()->image('cover.jpg');
        $adapter = \Mockery::mock(\League\Flysystem\FilesystemAdapter::class);
        $aws = new \Aws\S3\Exception\S3Exception('ACLs disabled', new \Aws\Command('PutObject'), [
            'code' => 'AccessControlListNotSupported',
        ]);
        $adapter->shouldReceive('writeStream')->once()->ordered()
            ->withArgs(fn ($path, $stream, $config) => $config->get('visibility') === 'public' && is_resource($stream))
            ->andThrow(\League\Flysystem\UnableToWriteFile::atLocation('cover.jpg', '', $aws));
        $adapter->shouldReceive('writeStream')->once()->ordered()
            ->withArgs(function ($path, $stream, $config) use ($upload) {
                $this->assertSame(file_get_contents($upload->getRealPath()), stream_get_contents($stream));

                return $config->get('ACL') === 'bucket-owner-full-control';
            });
        $driver = new \League\Flysystem\Filesystem($adapter, ['visibility' => 'public']);
        Storage::set('s3', new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter, ['throw' => false]));

        $stored = [];
        $path = app(WorkFiles::class)->store($upload, 'images/works/1/covers', $stored);
        $this->assertSame([$path], $stored);
        $this->assertStringStartsWith('images/works/1/covers/', $path);
    }

    public function test_upload_permission_error_is_preserved_without_retry_even_when_disk_swallows_errors(): void
    {
        $adapter = \Mockery::mock(\League\Flysystem\FilesystemAdapter::class);
        $aws = new \Aws\S3\Exception\S3Exception('Access denied', new \Aws\Command('PutObject'), [
            'code' => 'AccessDenied', 'message' => 'Missing s3:PutObject permission',
        ]);
        $failure = \League\Flysystem\UnableToWriteFile::atLocation('cover.jpg', '', $aws);
        $adapter->shouldReceive('writeStream')->once()->andThrow($failure);
        $driver = new \League\Flysystem\Filesystem($adapter);
        Storage::set('s3', new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter, ['throw' => false]));
        \Illuminate\Support\Facades\Log::shouldReceive('error')->once()->with('Work file upload failed', \Mockery::on(
            fn ($context) => $context['aws_error_code'] === 'AccessDenied'
        ));

        $stored = [];
        try {
            app(WorkFiles::class)->store(UploadedFile::fake()->image('cover.jpg'), 'images/works/1/covers', $stored);
            $this->fail('A failed upload must throw.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('S3: AccessDenied', $error->getMessage());
            $this->assertSame($failure, $error->getPrevious());
            $this->assertSame([], $stored);
        }
    }

    public function test_failed_cover_upload_keeps_existing_metadata_and_old_cover(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $old = "images/works/{$work->id}/covers/old.jpg";
        Storage::disk('s3')->put($old, 'old');
        $file = $work->files()->create(['part_title' => 'Original', 'part_cover_url' => Storage::disk('s3')->url($old)]);
        $this->mock(WorkFiles::class, function ($mock) {
            $mock->shouldReceive('store')->once()->andThrow(new \RuntimeException('S3 unavailable'));
            $mock->shouldNotReceive('deleteUrl');
        });
        \Illuminate\Support\Facades\Log::shouldReceive('error')->zeroOrMoreTimes();
        $this->postJson("/admin/work/{$work->id}/files/{$file->id}", [
            'part_title' => 'Changed', 'cover' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertStatus(500);
        $this->assertSame('Original', $file->fresh()->part_title);
        $this->assertSame(Storage::disk('s3')->url($old), $file->fresh()->part_cover_url);
        Storage::disk('s3')->assertExists($old);
    }

    public function test_upload_endpoint_saves_files(): void
    {
        $work = Work::create([]);
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $this->postJson("/admin/work/{$work->id}/files", [
            'files_urls' => [UploadedFile::fake()->image('photo.jpg')],
            'part_titles' => ['Part one'], 'part_covers' => [UploadedFile::fake()->image('cover.jpg')],
        ])->assertOk();
        $this->assertSame('Part one', $work->files()->first()->part_title);
        $this->assertCount(2, Storage::disk('s3')->allFiles());
    }

    public function test_upload_endpoint_requires_authentication(): void
    {
        $work = Work::create([]);
        $this->withoutExceptionHandling();
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->postJson("/admin/work/{$work->id}/files", []);
    }

    public function test_failed_append_rolls_back_records_and_cleans_storage(): void
    {
        \Illuminate\Support\Facades\Log::shouldReceive('error')->zeroOrMoreTimes();
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\EnsureUserHasAnyRole::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $work = Work::create([]);
        $this->mock(WorkFiles::class, function ($mock) use ($work) {
            $mock->shouldReceive('append')->once()->andReturnUsing(function ($model, $request, &$stored) use ($work) {
                $stored[] = "images/works/{$work->id}/partial.jpg";
                Storage::disk('s3')->put($stored[0], 'partial');
                $model->files()->create(['file_name' => 'partial']);
                throw new \RuntimeException('Simulated storage failure');
            });
        });
        $this->postJson("/admin/work/{$work->id}/files", ['files_urls' => [UploadedFile::fake()->image('photo.jpg')]])->assertStatus(500);
        $this->assertSame(0, $work->files()->count());
        $this->assertCount(0, Storage::disk('s3')->allFiles());
    }

    public function test_editor_partial_renders_translations_and_escapes_script_content(): void
    {
        app()->setLocale('fr');
        $html = view('admin.work-editor', ['meta' => ['work_form_options' => []], 'payload' => []])->render();
        $this->assertStringContainsString('Ajouter une œuvre', $html);
        $this->assertStringContainsString('workEditorConfig', $html);
        app()->setLocale('en');
        $html = view('admin.work-editor', ['meta' => ['work_form_options' => []], 'payload' => []])->render();
        $this->assertStringContainsString('Add a work', $html);
    }
}

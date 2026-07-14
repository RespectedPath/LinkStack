<?php

namespace Tests\Feature;

use App\Http\Controllers\AppearanceController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Admin panel user editor uploads. Historically these two file inputs
 * had NO validation and the background write skipped the sparse blob —
 * the same file/settings split-brain fixed on the customer path.
 */
class AdminUserEditTest extends TestCase
{
    use RefreshDatabase;

    /** High ids so real accounts' files can never be touched. */
    private int $nextId = 990200;

    private function backgroundDir(): string
    {
        return base_path('assets/img/background-img');
    }

    protected function tearDown(): void
    {
        foreach (range(990200, $this->nextId) as $id) {
            AppearanceController::removeBackgroundFileIfPresent($id);
            foreach (glob(base_path('assets/img') . '/' . $id . '_*') ?: [] as $f) {
                @unlink($f);
            }
        }
        parent::tearDown();
    }

    /** The fields editUser writes unconditionally — post them back so the target keeps them. */
    private function editPayload(User $target, array $overrides = []): array
    {
        return array_merge([
            'name'                    => $target->name,
            'email'                   => $target->email,
            'password'                => '',
            'littlelink_name'         => $target->littlelink_name,
            'littlelink_description'  => (string) $target->littlelink_description,
            'role'                    => $target->role,
            'theme'                   => (string) $target->theme,
        ], $overrides);
    }

    public function test_oversized_background_is_rejected(): void
    {
        $admin  = $this->makeUser(['id' => $this->nextId++, 'role' => 'admin']);
        $target = $this->makeUser(['id' => $this->nextId++]);

        $response = $this->actingAs($admin)->post(
            '/admin/edit-user/' . $target->id,
            $this->editPayload($target, [
                'background' => UploadedFile::fake()->image('huge.jpg', 800, 600)->size(3000),
            ])
        );

        $response->assertSessionHasErrors('background');
        $this->assertCount(0, glob($this->backgroundDir() . '/' . $target->id . '_*') ?: [], 'rejected upload must not write a file');
    }

    public function test_non_image_logo_is_rejected(): void
    {
        $admin  = $this->makeUser(['id' => $this->nextId++, 'role' => 'admin']);
        $target = $this->makeUser(['id' => $this->nextId++]);

        $this->actingAs($admin)->post(
            '/admin/edit-user/' . $target->id,
            $this->editPayload($target, [
                'image' => UploadedFile::fake()->create('malware.php', 10, 'text/php'),
            ])
        )->assertSessionHasErrors('image');
    }

    public function test_background_upload_keeps_file_and_blob_in_lockstep(): void
    {
        $admin  = $this->makeUser(['id' => $this->nextId++, 'role' => 'admin']);
        $target = $this->makeUser(['id' => $this->nextId++]);

        // A stale legacy-named file (stock admin uploader wrote {id}.{ext}).
        if (!is_dir($this->backgroundDir())) {
            @mkdir($this->backgroundDir(), 0755, true);
        }
        file_put_contents($this->backgroundDir() . '/' . $target->id . '.png', 'old-bytes');

        $this->actingAs($admin)->post(
            '/admin/edit-user/' . $target->id,
            $this->editPayload($target, [
                'background' => UploadedFile::fake()->image('new.jpg', 800, 600),
            ])
        )->assertRedirect('admin/users/all');

        // Exactly one background file: the new one; the legacy file is gone.
        $files = glob($this->backgroundDir() . '/' . $target->id . '*') ?: [];
        $this->assertCount(1, $files, 'old legacy-named file must be replaced, not accumulated');
        $this->assertStringContainsString($target->id . '_', basename($files[0]));

        // And the sparse blob records the override (both-or-neither).
        $target->refresh();
        $blob = json_decode((string) $target->theme_customization, true);
        $this->assertSame('image', $blob['background']['type'] ?? null, 'blob must record the admin-uploaded background');
        $this->assertStringContainsString('/assets/img/background-img/' . $target->id . '_', $blob['background']['image_url'] ?? '');
    }
}

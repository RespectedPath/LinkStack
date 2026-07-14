<?php

namespace Tests\Feature;

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Draft/publish isolation: edits go to the draft (live DB); the public
 * bio page renders the published snapshot until Publish is clicked.
 * ?preview=1 shows the owner their draft — and falls back to the
 * published version for anyone who is not the owner.
 */
class DraftPublishTest extends TestCase
{
    use RefreshDatabase;

    private function makePageWithHeading(string $title = 'Version One'): array
    {
        $user = $this->makeUser();

        $block = new Link();
        $block->user_id = $user->id;
        $block->button_id = 1;
        $block->type = 'heading';
        $block->title = $title;
        $block->link = '';
        $block->order = 0;
        $block->type_params = json_encode(['custom_html' => true]);
        $block->save();

        return [$user, $block];
    }

    public function test_public_page_shows_published_snapshot_not_draft(): void
    {
        [$user, $block] = $this->makePageWithHeading('Version One');

        // Publish, then edit the draft.
        $this->actingAs($user)->post('/studio/publish')->assertRedirect();
        $block->update(['title' => 'Version Two']);

        // Logged-out visitor sees the published content only.
        $this->post('/logout');
        $public = $this->get('/@' . $user->littlelink_name);
        $public->assertSuccessful();
        $public->assertSee('Version One');
        $public->assertDontSee('Version Two');
    }

    public function test_owner_preview_shows_the_draft(): void
    {
        [$user, $block] = $this->makePageWithHeading('Version One');

        $this->actingAs($user)->post('/studio/publish');
        $block->update(['title' => 'Version Two']);

        $this->actingAs($user)
            ->get('/@' . $user->littlelink_name . '?preview=1')
            ->assertSuccessful()
            ->assertSee('Version Two');
    }

    public function test_preview_param_does_not_leak_drafts_to_strangers(): void
    {
        [$user, $block] = $this->makePageWithHeading('Version One');

        $this->actingAs($user)->post('/studio/publish');
        $block->update(['title' => 'Secret Draft']);

        $stranger = $this->makeUser();
        $this->actingAs($stranger)
            ->get('/@' . $user->littlelink_name . '?preview=1')
            ->assertSuccessful()
            ->assertSee('Version One')
            ->assertDontSee('Secret Draft');
    }

    public function test_publishing_again_promotes_the_draft(): void
    {
        [$user, $block] = $this->makePageWithHeading('Version One');

        $this->actingAs($user)->post('/studio/publish');
        $block->update(['title' => 'Version Two']);
        $this->actingAs($user)->post('/studio/publish');

        $this->post('/logout');
        $this->get('/@' . $user->littlelink_name)
            ->assertSuccessful()
            ->assertSee('Version Two');
    }
}

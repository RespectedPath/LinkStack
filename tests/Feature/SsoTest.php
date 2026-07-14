<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mail Minted → LinkStack SSO bridge (routes/sso-mailminted.php).
 * Valid short-lived HS256 tokens log the mapped user in; everything
 * else lands back on /login unauthenticated.
 */
class SsoTest extends TestCase
{
    use RefreshDatabase;

    private function token(array $claims = [], ?string $secret = null): string
    {
        $payload = array_merge([
            'iss' => 'mailminted',
            'aud' => 'linkstack',
            'sub' => '1',
            'iat' => time(),
            'exp' => time() + 60,
        ], $claims);

        return JWT::encode($payload, $secret ?? env('MAILMINTED_SSO_SHARED_SECRET'), 'HS256');
    }

    public function test_valid_token_logs_the_user_in(): void
    {
        $user = $this->makeUser();

        $this->get('/sso/mailminted?token=' . $this->token(['sub' => (string) $user->id]))
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->get('/sso/mailminted?token=' . $this->token([
            'sub' => (string) $user->id,
            'iat' => time() - 3600,
            'exp' => time() - 3540,
        ]))->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_wrong_signature_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->get('/sso/mailminted?token=' . $this->token(['sub' => (string) $user->id], 'a-completely-different-32-byte-secret!!'))
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_wrong_issuer_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->get('/sso/mailminted?token=' . $this->token(['sub' => (string) $user->id, 'iss' => 'evil']))
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_wrong_audience_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->get('/sso/mailminted?token=' . $this->token(['sub' => (string) $user->id, 'aud' => 'other-app']))
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_unknown_user_is_rejected(): void
    {
        $this->get('/sso/mailminted?token=' . $this->token(['sub' => '86753090000']))
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_missing_token_is_rejected(): void
    {
        $this->get('/sso/mailminted')->assertRedirect('/login');
        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_carry_baseline_security_headers(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_content_security_policy_allows_assets_the_application_uses(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'A Content-Security-Policy header must be present.');
        // Baseline hardening directives.
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        // Inline Blade scripts/styles must keep working.
        $this->assertStringContainsString("'unsafe-inline'", $csp);
        // Google Fonts + Bootstrap Icons CDN.
        $this->assertStringContainsString('https://fonts.googleapis.com', $csp);
        $this->assertStringContainsString('https://fonts.gstatic.com', $csp);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $csp);
        // OpenStreetMap tiles load over https from several subdomains.
        $this->assertStringContainsString("img-src 'self' data: https:", $csp);
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $plain = $this->get(route('home'));
        $this->assertNull($plain->headers->get('Strict-Transport-Security'));

        $secure = $this->get('https://localhost');
        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $secure->headers->get('Strict-Transport-Security')
        );
    }
}

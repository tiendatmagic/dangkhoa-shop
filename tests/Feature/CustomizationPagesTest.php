<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomizationPagesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database connection is not available for tests: ' . $e->getMessage());
        }

        if (! Schema::hasTable('customizations')) {
            $this->markTestSkipped('Table customizations does not exist. Run migrations before running this test suite.');
        }
    }

    public function test_public_customization_includes_page_content_keys(): void
    {
        $response = $this->getJson('/api/customization');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'slides',
            'collections',
            'banner',
            'about_content',
            'contact_content',
        ]);
    }

    public function test_admin_customization_rejects_script_tag_in_page_content(): void
    {
        $this->withoutMiddleware();

        $payload = [
            'slides' => [],
            'collections' => [],
            'banner' => '',
            'about_content' => '<p>Hello</p><script>alert(1)</script>',
            'contact_content' => '<p>Contact</p>',
        ];

        $response = $this->postJson('/api/admin/customization', $payload);

        $response->assertStatus(422);
    }

    public function test_admin_customization_allows_basic_html_in_page_content(): void
    {
        $this->withoutMiddleware();

        if (! Schema::hasColumn('customizations', 'about_content') || ! Schema::hasColumn('customizations', 'contact_content')) {
            $this->markTestSkipped('Missing about/contact columns in customizations table. Run migrations before running this test.');
        }

        $payload = [
            'about_content' => '<h2>About</h2><p><strong>Bold</strong> text</p>',
            'contact_content' => '<h2>Contact</h2><p>Email: test@example.com</p>',
        ];

        $response = $this->postJson('/api/admin/customization', $payload);

        $response->assertStatus(200);

        $public = $this->getJson('/api/customization');
        $public->assertStatus(200);
        $public->assertJsonFragment(['about_content' => $payload['about_content']]);
        $public->assertJsonFragment(['contact_content' => $payload['contact_content']]);
    }
}

<?php

namespace Tests\Feature;

use Database\Seeders\UiUxTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyUiUxTaxonomyDeployTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_ui_ux_taxonomy_returns_ok_after_migrations(): void
    {
        config(['app.deploy_hook_token' => 'test-token']);

        $this->getJson('/deploy/verify-ui-ux-taxonomy', [
            'X-Deploy-Token' => 'test-token',
        ])
            ->assertOk()
            ->assertJson([
                'ok'                 => true,
                'ui_ux_category'     => true,
                'ui_ux_tags'         => count(UiUxTaxonomy::tags()),
                'expected_ui_ux_tags' => count(UiUxTaxonomy::tags()),
            ]);
    }
}

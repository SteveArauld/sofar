<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_default_locale(): void
    {
        $this->get('/')
            ->assertRedirect('/pt');
    }

    public function test_portuguese_home_ok(): void
    {
        $this->get('/pt')
            ->assertOk()
            ->assertSee('lang-switch', false)
            ->assertSee('>PT<', false)
            ->assertSee('>IT<', false);
    }

    public function test_italian_home_ok(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('lang-switch', false)
            ->assertSee('Sconti fino al 70%', false);
    }

    public function test_legacy_path_redirects_with_locale_prefix(): void
    {
        $this->get('/lojas')
            ->assertRedirect('/pt/lojas');
    }

    public function test_localized_institutional_page(): void
    {
        $this->get('/it/lojas')
            ->assertOk()
            ->assertSee('I nostri negozi', false);
    }
}

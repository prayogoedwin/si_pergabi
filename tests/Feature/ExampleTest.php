<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PERGABI');
        $response->assertSee('Perkumpulan Guru Agama Buddha Indonesia');
        $response->assertSee('Daftar');
        $response->assertSee('Masuk');
        $response->assertDontSee('Laravel has an incredibly rich ecosystem');
        $response->assertDontSee('Laracasts');
        $response->assertDontSee('Deploy now');
    }
}

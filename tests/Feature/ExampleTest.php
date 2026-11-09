<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman / sekarang daftar circle (PRD F8) — wajib login.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = \App\Models\User::create(['username' => 'homeuser', 'password' => 'password123']);

        $this->actingAs($user)->get('/')->assertStatus(200);
    }
}

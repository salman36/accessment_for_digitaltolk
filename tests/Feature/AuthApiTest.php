<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password123',
        ]);
        $register->assertStatus(201)->assertJsonStructure(['token']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'tester@example.com',
            'password' => 'password123',
        ]);
        $login->assertOk()->assertJsonStructure(['token']);
    }

    public function test_me_and_logout(): void
    {
        $reg = $this->postJson('/api/auth/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
        ])->assertStatus(201);
        $token = $reg->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me')->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout')->assertOk();
    }
}



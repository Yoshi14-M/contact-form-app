<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        $response = $this->get('/admin');
        // Assert
        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログイン画面を表示できる(): void
    {
        // Act
        $response = $this->get('/login');
        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function 正しい認証情報でログイン（管理画面が表示）できる(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        // Act
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        // Assert
        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 間違ったパスワードではログインできない(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        // Act
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function ログアウトできる(): void
    {
        // Arrange
        $user = User::factory()->create();
        // Act
        $response = $this->actingAs($user)->post('/logout');
        // Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

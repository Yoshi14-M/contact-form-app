<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テストメソッド実行前の共通セットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();
        // 認証を通過させるため、管理者ユーザーを作成してログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function タグの新規作成ができる(): void
    {
        // Arrange
        $tag = Tag::factory()->make()->toArray();
        // Act
        $response = $this->post(route('admin.tags.store'), $tag);
        // Assert
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', $tag);
    }

    /** @test */
    public function タグの編集画面が表示ができる(): void
    {
        // $this->withoutExceptionHandling();
        // Arrange
        $tag = Tag::factory()->create();
        // Act
        $response = $this->get(route('admin.tags.edit', $tag));
        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
    }

    /** @test */
    public function タグの更新ができる(): void
    {
        // Arange
        $tag = Tag::factory()->create();
        $updateTag = Tag::factory()->make()->toArray();
        // Act
        $response = $this->put(Route('admin.tags.update', $tag), $updateTag);
        // Aseert
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', $updateTag);
    }

    /** @test */
    public function タグが削除（関連する問い合わせの削除）できる(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        $contact->tags()->attach($tag);
        // Act
        $response = $this->delete(Route('admin.tags.destroy', $tag));
        // Assert
        $response->assertRedirect(Route('admin.index'));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        $this->assertDatabaseMissing('contact_tag', ['tag_id' => $tag->id]);
    }

    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        auth()->logout(); // ログアウト状態にする
        $tag = Tag::factory()->create();
        // Assert
        $this->post(route('admin.tags.store'), ['name' => 'テスト'])->assertRedirect(route('login'));
        $this->get(route('admin.tags.edit', $tag))->assertRedirect(route('login'));
        $this->put(route('admin.tags.update', $tag), ['name' => '更新'])->assertRedirect(route('login'));
        $this->delete(route('admin.tags.destroy', $tag))->assertRedirect(route('login'));
    }

    /** @test */
    public function タグ名が50文字以下の場合は登録できる(): void
    {
        // Act
        $response = $this->post(route('admin.tags.store'), [
            'name' => str_repeat('a', 50),
        ]);
        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tags', ['name' => str_repeat('a', 50)]);
    }

    /** @test */
    public function タグ名が51文字以上の場合はバリデーションエラーになる(): void
    {
        // Act
        $response = $this->post(route('admin.tags.store'), [
            'name' => str_repeat('a', 51),
        ]);
        // Assert
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function 新規登録時に既存のタグ名と重複している場合はバリデーションエラーになる(): void
    {
        // Arrange
        Tag::factory()->create(['name' => '既存タグ']);
        // Act
        $response = $this->post(route('admin.tags.store'), [
            'name' => '既存タグ',
        ]);
        // Assert
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function 更新時に自身のタグ名を維持して更新できる(): void
    {
        // Arrange
        $tag = Tag::factory()->create(['name' => '既存タグ']);
        // Act
        $response = $this->put(route('admin.tags.update', $tag), [
            'name' => '既存タグ',
        ]);
        // Assert
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function 更新時に他の既存タグ名と重複した場合はバリデーションエラーになる(): void
    {
        // Arrange
        Tag::factory()->create(['name' => 'タグA']);
        $tagB = Tag::factory()->create(['name' => 'タグB']);
        // Act
        $response = $this->put(route('admin.tags.update', $tagB), [
            'name' => 'タグA',
        ]);
        // Assert
        $response->assertSessionHasErrors(['name']);
    }
}

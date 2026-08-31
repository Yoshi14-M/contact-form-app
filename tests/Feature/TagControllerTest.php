<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\PostCondition;
use Route;
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
        //Arrange
        $tag = Tag::factory()->make()->toArray();
        //Act
        $respose = $this->post(route('admin.tags.store'), $tag);
        //Assert
        $respose->assertRedirect(Route('admin.index'));
        $this->assertDatabaseHas('tags', $tag);
    }
    /** @test */
    public function タグの編集画面が表示ができる(): void
    {
        $this->withoutExceptionHandling();
        //Arrange
        $tag = Tag::factory()->create();
        //Act
        $response = $this->get(route('admin.tags.edit', $tag));
        //Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
    }
    /** @test */
    public function タグの更新ができる(): void
    {
        //Arange
        $tag = Tag::factory()->create();
        $updateTag = Tag::factory()->make()->toArray();
        //Act
        $response = $this->put(Route('admin.tags.update', $tag), $updateTag);
        //Aseert
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', $updateTag);
    }
    /** @test */
    public function タグが削除（関連する問い合わせの削除）できる(): void
    {
        //Arrange
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        $contact->tags()->attach($tag);
        //Act
        $response = $this->delete(Route('admin.tags.destroy', $tag));
        //Assert
        $response->assertRedirect(Route('admin.index'));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        $this->assertDatabaseMissing('contact_tag', ['tag_id' => $tag->id]);
    }
}

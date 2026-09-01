<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト用データの作成
        $this->category = Category::factory()->create();
        $this->tag = Tag::factory()->create();
    }

    /** @test */
    public function お問い合わせ一覧取得できる(): void
    {
        // Arrange
        Contact::factory()->count(25)->create(['category_id' => $this->category->id]);
        // Act
        $response = $this->getJson('/api/v1/contacts');
        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'gender', 'email', 'tel', 'address', 'building', 'detail', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /** @test */
    public function 性別に不正値があればバリデーションエラーになる(): void
    {
        // Act
        $response = $this->getJson('/api/v1/contacts?gender=0');
        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function お問い合わせ詳細が取得できる(): void
    {
        // Arrange
        $contact = Contact::factory()->create(['category_id' => $this->category->id]);
        $contact->tags()->attach($this->tag->id);
        // Act
        $response = $this->getJson("/api/v1/contacts/{$contact->id}");
        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonPath('data.tags.0.id', $this->tag->id);
    }

    /** @test */
    public function お問い合わせ_i_dが存在しない場合404エラーが返される(): void
    {
        // Act
        $response = $this->getJson('/api/v1/contacts/99999');
        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    /** @test */
    public function お問い合わせが作成できる(): void
    {
        // Arrange
        $payload = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-1-1',
            'building' => 'テストビル',
            'category_id' => $this->category->id,
            'detail' => 'お問い合わせ内容テキスト',
            'tag_ids' => [$this->tag->id],
        ];
        // Act
        $response = $this->postJson('/api/v1/contacts', $payload);
        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['email' => 'yamada@example.com']);
        $this->assertDatabaseHas('contact_tag', ['tag_id' => $this->tag->id]);
    }

    /** @test */
    public function お問い合わせが空白のときバリデーションエラーになる(): void
    {
        // Act
        $response = $this->postJson('/api/v1/contacts', []);
        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'gender', 'email', 'tel', 'address', 'category_id', 'detail']);
    }

    /** @test */
    public function お問い合わせの更新ができる(): void
    {
        // Arrange
        $contact = Contact::factory()->create(['category_id' => $this->category->id]);

        $updatePayload = [
            'first_name' => '次郎',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08098765432',
            'address' => '大阪府大阪市1-2-3',
            'category_id' => $this->category->id,
            'detail' => '更新後の内容テキスト',
        ];
        // Act
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updatePayload);
        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '次郎',
            'email' => 'sato@example.com',
        ]);
    }

    /** @test */
    public function お問い合わせが削除できる(): void
    {
        // Arrange
        $contact = Contact::factory()->create(['category_id' => $this->category->id]);
        // Act
        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");
        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}

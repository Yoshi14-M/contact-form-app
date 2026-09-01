<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ページネーションされて表示される(): void
    {
        // Arrange
        $user = User::factory()->create();
        Contact::factory()->count(10)->create();
        // Act
        $response = $this->actingAs($user)->get(route('admin.index'));
        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts instanceof LengthAwarePaginator
                && $contacts->count() === 7   // 1ページ目の表示件数が7件であること
                && $contacts->total() === 10; // 総データ数が10件として認識されていること
        });
    }

    /** @test */
    public function 詳細画面が表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        // Act
        $response = $this->actingAs($user)->get(route('admin.contacts.show', $contact));
        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact', function ($viewContact) use ($contact) {
            return $viewContact->id === $contact->id;
        });
    }

    /** @test */
    public function 問い合わせが削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        // Act
        $response = $this->actingAs($user)->delete(route('admin.contacts.destroy', $contact));
        // Assert
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('admin.index'));
        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 新着順の問い合わせ一覧をダウンロードできる(): void
    {
        // $this->withoutExceptionHandling();
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '商品のお届けについて']);

        $oldContact = Contact::factory()->create([ // 古いデータを作成
            'category_id' => $category->id,
            'created_at' => now()->subDays(2),
        ]);
        $newContact = Contact::factory()->create([ // 新しいデータを作成
            'category_id' => $category->id,
            'created_at' => now(),
        ]);
        // Act
        $response = $this->actingAs($user)->get('/contacts/export');
        // Assert
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // BOM（\xEF\xBB\xBF）が含まれているか検証
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // 新しいデータが古いデータより前に出力されているか（新着順）の検証
        $newPos = strpos($content, (string) $newContact->id);
        $oldPos = strpos($content, (string) $oldContact->id);
        $this->assertTrue($newPos < $oldPos);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせフォームが表示される(): void
    {
        // Arrange
        $category = Category::create(['content' => '商品トラブル']);
        $tag = Tag::create(['name' => '不具合報告']);
        // Act
        $response = $this->get('/');
        // Assert
        $response->assertStatus(200)
            ->assertViewIs('contact.index') // 問い合わせフォームが表示
            ->assertViewHas('categories') // Arrangeで登録したデータが表示
            ->assertViewHas('tags')
            ->assertSee('商品トラブル')
            ->assertSee('不具合報告');
    }

    /** @test */
    public function 確認画面が表示される(): void
    {
        // Arrange
        $category = Category::create(['content' => '商品トラブル']);
        $tag = Tag::create(['name' => '不具合報告']);

        $formData = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '問い合わせのテスト詳細内容です。',
            'tag_ids' => [$tag->id],
        ];
        // Act
        $response = $this->post('/contacts/confirm', $formData);
        // Assert
        $response->assertStatus(200)
            ->assertViewIs('contact.confirm')
            ->assertSee('山田')
            ->assertSee('太郎')
            ->assertSee('yamada@example.com')
            ->assertSee('商品トラブル')
            ->assertSee('不具合報告');
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        // Act
        $response = $this->get('/thanks');
        // Assert
        $response->assertStatus(200)
            ->assertViewIs('contact.thanks');
    }

    /** @test */
    public function 問い合わせがデータが作成される(): void
    {
        // Arrange
        $category = Category::create(['content' => '商品トラブル']);
        $tag = Tag::create(['name' => '不具合報告']);

        $formData = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '問い合わせのテスト詳細内容です。',
            'tag_ids' => [$tag->id],
        ];
        // Act
        $response = $this->post('/contacts', $formData);
        // Assert
        // contactsテーブルに基本データが保存されている
        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $category->id,
        ]);
        // 中間テーブル (contact_tag) に紐づいている
        $contact = Contact::where('email', 'yamada@example.com')->first();
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
        // thanks画面にリダイレクトされる
        $response->assertRedirect('/thanks');
    }

    /** @test */
    public function 電話番号の不正値などでバリデーションエラーとなる(): void
    {
        // Arrange
        $category = Category::create(['content' => '商品のお届けについて']);

        $invalidData = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678', // ハイフンありはNG
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'テスト詳細',
        ];
        // Act
        $response = $this->post('/contacts/confirm', $invalidData);
        // Assert
        $response->assertSessionHasErrors(['tel']); // 電話番号エラーが含まれている
    }
}

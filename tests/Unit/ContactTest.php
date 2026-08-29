<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 問い合わせが一つのカテゴリ紐づいている(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        // インスタンスの型およびカテゴリIDの一致を検証
        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertEquals($category->id, $contact->category->id);
    }

    /** @test */
    public function 問い合わせが複数のタグと紐づいている(): void
    {
        $contact = Contact::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        // タグを同期（sync）
        $contact->tags()->sync($tags->pluck('id'));

        // 紐づいたタグの件数およびデータ内容を検証
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tags->first()));
    }

    /** @test */
    public function 性別が正しく変換されている(): void
    {
        $contactMale = Contact::factory()->make(['gender' => 1]);
        $contactFemale = Contact::factory()->make(['gender' => 2]);
        $contactOther = Contact::factory()->make(['gender' => 3]);

        // アクセサ経由で取得できる文字列の検証
        $this->assertEquals('男性', $contactMale->gender_label);
        $this->assertEquals('女性', $contactFemale->gender_label);
        $this->assertEquals('その他', $contactOther->gender_label);
    }
}

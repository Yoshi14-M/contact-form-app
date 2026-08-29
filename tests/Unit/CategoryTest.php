<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 一つのカテゴリに複数の問い合わせが紐づいている(): void
    {
        $category = Category::factory()->create();
        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        // 取得件数が正しく3件であること、および作成したインスタンスが含まれることを検証
        $this->assertCount(3, $category->contacts);
        $this->assertTrue($category->contacts->contains($contacts->first()));
    }
}

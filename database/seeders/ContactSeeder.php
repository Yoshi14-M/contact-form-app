<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        // カテゴリーを取得
        $categories = Category::all();
        // タグを取得
        $tags = Tag::all();

        // 20件と問い合わせを投入
        for ($i = 0; $i < 20; $i++) {
            $contact = Contact::factory()->create([
                'category_id' => $categories->random()->id,
            ]);

            // 既存のタグからランダムに1〜3件選択して多対多リレーションで紐付け
            $randomTags = $tags->random(rand(1, 3))->pluck('id');
            $contact->tags()->attach($randomTags);
        }
    }
}

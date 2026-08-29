<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        //問い合わせ分類を5件投入
        $categories = [
            '商品のお届けについて',
            '商品の交換について',
            '商品トラブル',
            'ショップへのお問い合わせ',
            'その他',
        ];
        //カテゴリーを作成
        foreach ($categories as $content) {
            Category::create([
                'content' => $content,
            ]);
        }
    }
}
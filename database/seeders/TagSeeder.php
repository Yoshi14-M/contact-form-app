<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        //タグ候補を5件投入
        $tags = [
            '質問',
            '要望',
            '不具合報告',
            'ご意見',
            'その他',
        ];
        //タグを作成
        foreach ($tags as $name) {
            Tag::create([
                'name' => $name,
            ]);
        }
    }
}
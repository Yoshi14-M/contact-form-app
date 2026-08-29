<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグに複数のお問い合わせが紐付いている(): void
    {
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(2)->create();

        // 中間テーブルへリレーション紐付け
        $tag->contacts()->attach($contacts->pluck('id'));

        // 紐付けられたお問い合わせの件数およびインスタンスを検証
        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->contains($contacts->first()));
    }
}

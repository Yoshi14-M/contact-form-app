<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;
    public function definition(): array
    {
        return [
            // リレーション先のカテゴリ（親要素がなければ自動生成）
            'category_id' => Category::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            // 1:男性, 2:女性, 3:その他
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail(),
            // 10〜11桁のハイフンなし電話番号ルールに準拠（例: 09012345678 形式）
            'tel' => fake()->numerify('090########'),
            'address' => fake()->address(),
            // nullable項目のため optional でランダム（70%の確率で入る）に生成する例
            'building' => fake()->optional(0.7)->secondaryAddress(),
            // 120文字以内の必須要件に適合する本文
            'detail' => fake()->realText(100),
        ];
    }
}

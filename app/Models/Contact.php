<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    /**複数代入可能な属性
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    /**
     * この問い合わせを所有するカテゴリーを取得
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * この問い合わせが属するタグを取得
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * 性別ラベルを取得
     */
    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            1 => '男性',
            2 => '女性',
            3 => 'その他',
            default => '',
        };
    }

    /**
     * 検索フィルタリング用のローカルスコープ
     * （問い合わせ一覧表示およびCSVファイル出力で使用。）
     */
    public function scopeSearch(Builder $query, array $filters): Builder
    {
        // キーワード検索
        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 性別検索 (0は「全て」扱いのため除外)
        if (isset($filters['gender']) && $filters['gender'] != 0) {
            $query->where('gender', $filters['gender']);
        }

        // カテゴリー検索
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // 問い合わせ日時検索
        if (! empty($validated['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->latest(); // 新着順に並べ替え
    }
}

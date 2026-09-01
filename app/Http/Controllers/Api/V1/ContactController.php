<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     * お問い合わせ一覧
     */
    public function index(IndexContactRequest $request)
    {
        // Eagerローディング
        $query = Contact::with(['category', 'tags']);

        // キーワード検索
        if ($keyword = $request->input('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        // 性別検索
        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }
        // カテゴリー検索
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        // 問い合わせ日時検索
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        // ページネーション
        $perPage = $request->input('per_page', 20); // デフォルト20件
        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     * お問い合わせ作成（DB保存とレスポンス整形）
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        // contactsテーブルに保存
        $contact = Contact::create($request->validated());

        // 中間テーブル(contact_tag)へ保存
        /**選択されたタグ以外が送信されくる可能性があるため、タグがある場合のみ保存
         * ApiでのJSONで送信、デベロッパーツールでの改ざん、タグ削除との同時実行など。
         */
        if (isset($validated['tag_ids'])) {
            $contact->tags()->sync($validated['tag_ids']);
        }

        // Eagerローディング
        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     * お問い合わせ詳細
     */
    public function show(Contact $contact)
    {
        // Eagerローディング
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     * 問い合わせの更新
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();

        $contact->update($validated);

        $tagIds = $validated['tag_ids'] ?? [];
        $contact->tags()->sync($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     * お問い合わせ削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}

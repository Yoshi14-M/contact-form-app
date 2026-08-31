<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * 問い合わせフォームを表示
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * 入力内容の確認画面を表示
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        // カテゴリーおよびオブジェクトを取得
        $category = Category::find($validated['category_id']);
        $tags = isset($validated['tag_ids']) ? Tag::findMany($validated['tag_ids']) : collect();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    /**
     * お問い合わせ送信処理（DB保存とリダイレクト）
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

        // サンクスページへリダイレクト
        return Redirect()->route('contact.thanks');
    }

    /**
     * サンクスページの表示
     * フォームの再送信により、二重登録を防ぐ。
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}

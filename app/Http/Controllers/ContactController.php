<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
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

    /**
     * Export a listing of the resource.
     * お問い合わせのCSV出力
     */
    public function export(ExportContactRequest $request)
    {
        // 検索ロジック呼び出して全件取得
        $query = Contact::with('category')
            ->search($request->validated());

        $filename = 'contacts_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'w');

            // Excel文字化け防止用BOM
            fwrite($stream, "\xEF\xBB\xBF");

            // CSVヘッダー
            fputcsv($stream, [
                'ID',
                '氏名',
                '性別',
                'メールアドレス',
                '電話番号',
                '住所',
                '建物名',
                'カテゴリ',
                'お問い合わせ内容',
                '作成日時',
            ]);

            // 1件ずつ取得して書き出し
            foreach ($query->cursor() as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    $contact->first_name.' '.$contact->last_name,
                    $contact->gender_label,
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category?->content ?? '',
                    $contact->detail,
                    $contact->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

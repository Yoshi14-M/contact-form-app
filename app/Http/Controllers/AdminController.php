<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     * 問い合わせ一覧を表示
     */
    public function index(IndexContactRequest $request)
    {
        // バリデーション
        $validated = $request->validated();

        // 検索ロジックを呼び出してページネーション
        $contacts = Contact::with(['category', 'tags'])
            ->search($validated)
            ->paginate(7)
            ->appends($validated);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * 問い合わせ詳細を表示
     */
    public function show(Contact $contact)
    {
        // Eagerローディング
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * お問い合わせの削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}

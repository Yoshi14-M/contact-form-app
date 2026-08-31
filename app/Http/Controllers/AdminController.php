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
        //Eagerローディング
        $query = Contact::with(['category', 'tags']);

        //キーワード検索
        if ($keyword = $request->input('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        //性別検索
        if ($gender = $request->input('gender')) {
            if ($gender != '0') {
                $query->where('gender', $gender);
            }
        }
        //カテゴリー検索
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        //問い合わせ日時検索
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        //ページネーション
        $contacts = $query->paginate(7)->appends($request->query());
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
        //Eagerローディング
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
     * 検索条件のクリア
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('admin.index');
    }
}

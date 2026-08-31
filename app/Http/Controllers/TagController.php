<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
     * タグの新規作成（DBへ保存）
     */
    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * タグの修正画面を表示
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     * タグの修正（DBを更新）
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     * タグを削除
     */
    public function destroy(Tag $tag)
    {
        /**
         * マイグレーションのcasecadeにより、タグに紐づく問い合わせも併せて削除
         */
        $tag->delete();

        return redirect()->route('admin.index')
            ->with('success', 'タグを削除しました');
    }
}

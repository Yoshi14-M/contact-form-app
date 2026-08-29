<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//仮ルート（お問い合わせ機能実装時に置き換え）
Route::get('/', function () {
    return 'お問い合わせ入力ページ（準備中）';
});

// 仮ルート（管理画面実装時に置き替え）
Route::middleware('auth')->group(function () {
    Route::get('/admin', fn() => '管理画面（準備中）')->name('admin.index');
});

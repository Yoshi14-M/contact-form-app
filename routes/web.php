<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
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

//お問い合わせフォーム（一般公開ルート）
Route::get('/', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

/** 
 * 管理画面（認証ルート）
 * prefix('admin')->name('admin.')でグループと結合して'admin.'を省略。
 */
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    //管理画面のCRUDルート(仮ルート)
    Route::get('/', fn() => '管理画面（準備中）')->name('index');
    //タグのCRUDルート
    Route::resource('tags', TagController::class)
        ->only(['store', 'edit', 'update', 'destroy',]);
});

<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TimingTagController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ（HOME）
Route::get('/', [HomeController::class, 'index'])->name('home');

// Laravel Breeze関連のルーティング（認証が必要なルートのグループ）
// ★このグループの中に、認証が必要な全てのルートを配置します★
Route::middleware('auth')->group(function () {
    // プロフィール関連のルート
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 薬に関するルーティング (Medications) - 認証が必要な場合
    Route::get('/medications',[MedicationController::class,'index'])->name('medications.index');
    Route::get('/medications/create',[MedicationController::class,'create'])->name('medications.create');
    Route::post('/medications',[MedicationController::class,'store'])->name('medications.store');
    Route::get('/medications/{medication}', [MedicationController::class, 'show'])->name('medications.show');
    Route::get('/medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
    Route::put('/medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
    Route::patch('/medications/{medication}', [MedicationController::class, 'update']);
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');

    // 服用タイミングに関するルーティング (TimingTags) - 認証が必要な場合
    Route::resource('timing_tags',TimingTagController::class);

    // postsリソースルート - 認証が必要な場合
    // ★先に個別のルートを定義してからリソースルートを定義することで、競合を避けます★
    // カレンダー表示 (posts/calendar)
    Route::get('/posts/calendar', [PostController::class, 'calendar'])->name('posts.calendar');

    // 日付ごとの投稿詳細ページ (posts/date/{date})
    Route::get('/posts/date/{date}', [PostController::class, 'showDailyRecords'])->name('posts.daily_records');

    // postsリソースルート（posts/{post} のような汎用的なパスを含むため、上記より後に来る）
    Route::resource('posts', PostController::class);

    // ダッシュボード
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard'); // 'auth' はグループで適用済みなので、'verified' のみでOK
});

require __DIR__.'/auth.php'; // Breezeの認証ルート
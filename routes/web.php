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


//薬に関するルーティング (Medications)
Route::get('/medications',[MedicationController::class,'index'])->name('medications.index');
Route::get('/medications/create',[MedicationController::class,'create'])->name('medications.create');
Route::post('/medications',[MedicationController::class,'store'])->name('medications.store');
Route::get('/medications/{medication}', [MedicationController::class, 'show'])->name('medications.show');
Route::get('/medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
Route::put('/medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
Route::patch('/medications/{medication}', [MedicationController::class, 'update']);
Route::delete('/medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');


// ★★★ここが重要：posts.calendar ルートを resource の前に移動★★★
Route::get('/posts/calendar', [PostController::class, 'calendar'])->name('posts.calendar');
Route::resource('posts', PostController::class); // posts.calendar より後に来る


// 服用タイミングに関するルーティング (TimingTags)
Route::resource('timing_tags',TimingTagController::class);


// Laravel Breeze関連のルーティング
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
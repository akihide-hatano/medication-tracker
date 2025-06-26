<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Medication;
use App\Models\TimingTag;
use Illuminate\Http\Request; // Request クラスを忘れずにインポート
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
// クエリビルダーの初期化
        $query = Post::with(['user', 'postMedicationRecords.medication', 'timingTags'])
                     ->orderBy('post_date', 'desc');

        // フィルターパラメータをチェック
        if ($request->has('filter')) {
            $filter = $request->input('filter');

            if ($filter === 'not_completed') {
                $query->where('all_meds_taken', false);
                // もし reason_not_taken が空でないものも絞り込むなら追加
                // $query->whereNotNull('reason_not_taken')->where('reason_not_taken', '!=', '');
            }
            // 将来的に他のフィルターを追加する場合、ここにelse ifで追加
        }

        $posts = $query->get();

        return view('posts.index', compact('posts'));
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
     */
    public function show(string $id)
    {
        //
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
     */
    public function destroy(string $id)
    {
        //
    }

     public function calendar(Request $request)
    {
        // カレンダー表示の基準となる年月を取得
        // デフォルトは現在月
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $date = Carbon::createFromDate($year, $month, 1);

        // 特定のユーザー（例：user_id = 1）のその月の投稿データを取得
        // 実際のアプリケーションでは Auth::id() を使用するか、ユーザーIDを動的に渡す
        $userId = 1; // とりあえず user_id が1のユーザーのデータを表示
        // $userId = Auth::id(); // 認証機能があればこちらを使う

        $posts = Post::where('user_id', $userId)
                     ->whereMonth('post_date', $month)
                     ->whereYear('post_date', $year)
                     ->get();

        // カレンダー表示用に、日付ごとの服薬状況を連想配列にまとめる
        $medicationStatusByDay = [];
        foreach ($posts as $post) {
            $day = Carbon::parse($post->post_date)->day;
            // all_meds_taken が true なら 'completed'、false なら 'not_completed'
            $medicationStatusByDay[$day] = $post->all_meds_taken ? 'completed' : 'not_completed';
        }

        // ビューに渡すデータ
        return view('posts.calendar', compact('date', 'medicationStatusByDay'));
    }
}

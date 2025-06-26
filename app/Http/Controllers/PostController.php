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
}

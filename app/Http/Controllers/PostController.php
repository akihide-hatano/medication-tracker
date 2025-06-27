<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Medication;
use App\Models\TimingTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * 投稿の一覧ページを表示
     * GET /posts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // ★★★ここを修正：timingTags から timingTag に変更★★★
        $query = Post::with(['user', 'postMedicationRecords.medication', 'postMedicationRecords.timingTag'])
                    ->orderBy('post_date', 'desc');

        if ($request->has('filter')) {
            $filter = $request->input('filter');
            if ($filter === 'not_completed') {
                $query->where('all_meds_taken', false);
            }
        }
        $posts = $query->get();
        return view('posts.index', compact('posts'));
    }

    /**
     * 新しい投稿の作成フォームを表示
     * GET /posts/create
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $medications = Medication::all();
        $timingTags = TimingTag::all();
        return view('posts.create', compact('medications', 'timingTags'));
    }

    /**
     * 新しい投稿をデータベースに保存
     * POST /posts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // ★★★修正: all_meds_taken と is_completed のバリデーションを nullable に変更★★★
        // (hidden input と value="0" があるので、理論的には required|boolean で問題ないはずですが、
        //  もし dd() が表示されないなら、これが原因の可能性があります)
        $rules = [
            'post_date' => 'required|date',
            'content' => 'nullable|string|max:1000',
            'all_meds_taken' => 'nullable|boolean', // required から nullable に変更
            'reason_not_taken' => 'nullable|string|max:500',
            'medications' => 'required|array|min:1',
            'medications.*.medication_id' => 'required|exists:medications,medication_id',
            'medications.*.timing_tag_id' => 'required|exists:timing_tags,timing_tag_id',
            'medications.*.is_completed' => 'nullable|boolean', // required から nullable に変更
        ];

        // all_meds_taken が false の場合のみ reason_not_taken を必須にする
        // ここでは `$request->input()` を使うため、nullable|boolean にしても機能するはず
        if (!$request->input('all_meds_taken')) {
            $rules['reason_not_taken'] = 'required|string|max:500';
        }

        // ★★★重要: バリデーション前に生のリクエストデータを確認します★★★
        dd($request->all()); // この行を有効にする

        try {
            // dd() でデータを確認した後、この $request->validate($rules); を有効に戻してください。
            // $validatedData = $request->validate($rules);

            DB::beginTransaction();

            $post = new Post();
            $post->user_id = Auth::id();
            $post->post_date = $validatedData['post_date']; // dd($request->all()) が有効な間はコメントアウト
            $post->content = $validatedData['content'] ?? null; // dd($request->all()) が有効な間はコメントアウト
            $post->all_meds_taken = $validatedData['all_meds_taken']; // dd($request->all()) が有効な間はコメントアウト
            $post->reason_not_taken = $validatedData['reason_not_taken'] ?? null; // dd($request->all()) が有効な間はコメントアウト
            $post->save();

            foreach ($validatedData['medications'] as $medicationRecord) {
                $post->postMedicationRecords()->create([
                    'medication_id' => $medicationRecord['medication_id'],
                    'timing_tag_id' => $medicationRecord['timing_tag_id'],
                    'is_completed' => $medicationRecord['is_completed'],
                ]);
            }

            DB::commit();
            return redirect()->route('posts.index')->with('success', '新しい投稿が正常に作成されました。');
        } catch (\Exception | \Throwable $e) {
            DB::rollBack();
            Log::error('投稿作成エラー: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', '投稿の作成中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * 特定の投稿の詳細を表示
     * GET /posts/{post}
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Post $post)
    {
        $post->load(['user', 'postMedicationRecords.medication', 'timingTags']);
        return view('posts.show', compact('post'));
    }

    /**
     * 特定の投稿の編集フォームを表示
     * GET /posts/{post}/edit
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Post $post)
    {
        $medications = Medication::all();
        $timingTags = TimingTag::all();

        $selectedMedications = $post->postMedicationRecords->mapWithKeys(function ($pmr) {
            return [
                $pmr->medication_id => [
                    'id' => $pmr->medication_id,
                    'name' => $pmr->medication->medication_name,
                    'timing_tags' => $pmr->timingTags->mapWithKeys(function ($tag) {
                        return [
                            $tag->timing_tag_id => [
                                'id' => $tag->timing_tag_id,
                                'name' => $tag->timing_name,
                                'is_completed' => $tag->pivot->is_completed,
                            ]
                        ];
                    })->toArray(),
                ]
            ];
        })->toArray();

        return view('posts.edit', compact('post', 'medications', 'timingTags', 'selectedMedications'));
    }

    /**
     * 特定の投稿をデータベースで更新
     * PUT/PATCH /posts/{post}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Post $post)
    {
        $validatedData = $request->validate([
            'post_date' => 'required|date',
            'all_meds_taken' => 'boolean',
            'reason_not_taken' => 'nullable|string|max:500',
            'content' => 'nullable|string|max:1000',
            'medications' => 'nullable|array',
            'medications.*.medication_id' => 'required_with:medications|exists:medications,medication_id',
            'medications.*.timing_tags' => 'nullable|array',
            'medications.*.timing_tags.*.timing_tag_id' => 'required_with:medications.*.timing_tags|exists:timing_tags,timing_tag_id',
            'medications.*.timing_tags.*.is_completed' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $post->update([
                'post_date' => $validatedData['post_date'],
                'all_meds_taken' => $request->has('all_meds_taken'),
                'reason_not_taken' => $validatedData['reason_not_taken'] ?? null,
                'content' => $validatedData['content'] ?? null,
            ]);

            // 既存の関連レコードを全て削除してから再作成
            foreach ($post->postMedicationRecords as $pmr) {
                $pmr->timingTags()->detach();
                $pmr->delete();
            }

            if (isset($validatedData['medications'])) {
                foreach ($validatedData['medications'] as $medicationData) {
                    $medicationId = $medicationData['medication_id'];
                    $postMedicationRecord = $post->postMedicationRecords()->create([
                        'medication_id' => $medicationId,
                        'is_completed' => false,
                    ]);

                    if (isset($medicationData['timing_tags'])) {
                        $pivotData = [];
                        foreach ($medicationData['timing_tags'] as $timingTagData) {
                            $timingTagId = $timingTagData['timing_tag_id'];
                            $isCompleted = isset($timingTagData['is_completed']) ? (bool)$timingTagData['is_completed'] : false;
                            $pivotData[$timingTagId] = ['is_completed' => $isCompleted];
                        }
                        $postMedicationRecord->timingTags()->attach($pivotData);
                    }
                }
            }
            DB::commit();
            return redirect()->route('posts.show', $post->post_id)->with('success', '投稿が正常に更新されました！');
        } catch (\Exception | \Throwable $e) { // Throwable を追加
            DB::rollBack();
            Log::error('投稿更新エラー: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', '投稿の更新中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * 特定の投稿をデータベースから削除
     * DELETE /posts/{post}
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Post $post)
    {
        DB::beginTransaction();
        try {
            foreach ($post->postMedicationRecords as $pmr) {
                $pmr->timingTags()->detach();
                $pmr->delete();
            }
            $post->delete();
            DB::commit();
            return redirect()->route('posts.index')->with('success', '投稿が正常に削除されました！');
        } catch (\Exception | \Throwable $e) { // Throwable を追加
            DB::rollBack();
            Log::error('投稿削除エラー: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', '投稿の削除中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * 服薬状況をカレンダー形式で表示
     * GET /posts/calendar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function calendar(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $date = Carbon::createFromDate($year, $month, 1);

        $userId = 1; // とりあえず user_id が1のユーザーのデータを表示

        $userExists = User::where('id', $userId)->exists();
        if (!$userExists) {
             Log::error("User ID {$userId} not found for calendar display.");
             return redirect()->route('home')->with('error', 'カレンダー表示に必要なユーザーが見つかりません。');
        }

        // postMedicationRecords.medication と postMedicationRecords.timingTag を eager load する
        // ★ここを修正しました：PostMedicationRecordsのrelationもloadしてmedicationとtimingTagを取るように
        $posts = Post::with(['postMedicationRecords.medication', 'postMedicationRecords.timingTag'])
                     ->where('user_id', $userId)
                     ->whereMonth('post_date', $month)
                     ->whereYear('post_date', $year)
                     ->get();

        // ★★★ここを修正：medicationStatusByDay の構造を JavaScript が期待するオブジェクト形式に戻す★★★
        $medicationStatusByDay = [];
        foreach ($posts as $post) {
            $day = Carbon::parse($post->post_date)->day;
            
            $status = $post->all_meds_taken ? 'completed' : 'not_completed';
            $details = [
                'status' => $status
            ];

            if ($post->all_meds_taken) {
                // 服用完了の場合、関連する薬の名前のリストを取得
                // 薬の名前は PostMedicationRecord ごとに TimingTag も含めて取得する形にするため、少しロジックを変更
                $medicationInfo = [];
                foreach ($post->postMedicationRecords as $record) {
                    $medName = $record->medication->medication_name ?? '不明な薬';
                    $timingName = $record->timingTag->timing_name ?? 'タイミングなし';
                    $isCompleted = $record->is_completed ? '完了' : '未完了';
                    $medicationInfo[] = "{$medName} ({$timingName}: {$isCompleted})";
                }
                $details['medications'] = $medicationInfo; // 全て服用済みの日には全薬の完了状況を詳細に表示
            } else {
                // 服用未完了の場合、理由を取得
                $details['reason'] = $post->reason_not_taken;
                // 未完了の場合も薬の情報を簡潔に表示したい場合はここに追加
            }
            $medicationStatusByDay[$day] = $details;
        }
        // ★★★ここまで修正★★★

        return view('posts.calendar', compact('date', 'medicationStatusByDay'));
    }

 public function showDailyRecords(string $dateString)
    {
    try {
            $date = Carbon::parse($dateString);
        } catch (\Exception $e) {
            return redirect()->route('posts.calendar')->with('error', '無効な日付が指定されました。');
        }

        $userId = 1;

        if (!User::where('id', $userId)->exists()) {
             Log::error("User ID {$userId} not found for daily records display.");
             return redirect()->route('home')->with('error', '投稿詳細表示に必要なユーザーが見つかりません。');
        }

        // ★★★ここを修正：timingTags から timingTag に変更★★★
        $posts = Post::with([
            'user',
            'postMedicationRecords.medication',
            'postMedicationRecords.timingTag' // timingTag (単数形) をロード
        ])
        ->where('user_id', $userId)
        ->whereDate('post_date', $date)
        ->orderBy('post_date', 'desc')
        ->get();

        return view('posts.daily_detail', compact('posts', 'date'));
        }
}
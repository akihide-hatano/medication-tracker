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
use Illuminate\Support\Collection;
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

    // ここに追記して、dd() で中身を確認
    // dd($medications); // ここで実行が止まり、データ構造が表示されます
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
        $validatedData = $request->validate([
            'post_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:1000'],
            'all_meds_taken' => ['required', 'boolean'],
            'reason_not_taken' => ['nullable', 'string', 'max:500', 'required_if:all_meds_taken,0'],
            'medications' => ['required', 'array', 'min:1'],
            'medications.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'medications.*.timing_tag_id' => ['required', 'exists:timing_tags,timing_tag_id'],
            'medications.*.is_completed' => ['required', 'boolean'],
            'medications.*.dosage' => 'nullable|string|max:255', // dosageは文字列として受け取る
        ]);

        try {
            DB::beginTransaction();

            $post = new Post();
            $post->user_id = Auth::id(); // 認証ユーザーのIDをセット
            $post->post_date = $validatedData['post_date'];
            $post->content = $validatedData['content'] ?? null;
            $post->all_meds_taken = $validatedData['all_meds_taken'];
            $post->reason_not_taken = $validatedData['reason_not_taken'] ?? null;
            $post->save();

            foreach ($validatedData['medications'] as $medicationRecord) {
                $post->postMedicationRecords()->create([
                    'medication_id' => $medicationRecord['medication_id'],
                    'timing_tag_id' => $medicationRecord['timing_tag_id'],
                    'is_completed' => $medicationRecord['is_completed'],
                    'taken_dosage' => $medicationRecord['dosage'] ?? null, // ここで taken_dosage を保存
                    'taken_at' => now(), // 服用日時を記録
                ]);
            }

            DB::commit();
            return redirect()->route('posts.index')->with('success', '新しい投稿が正常に作成されました！');
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
        // 関連するリレーションをEagerロード
        $post->load(['user', 'postMedicationRecords.medication', 'postMedicationRecords.timingTag']);

        // TimingTagをcategory_orderとtiming_tag_idで事前にソートして取得
        // これがカテゴリおよびその中のタイミングの表示順を制御する「マスター」順序となる
        $orderedTimingTags = TimingTag::orderBy('category_order')
                                    ->orderBy('timing_tag_id') // timing_nameではなくIDでソートして一貫性を保つ
                                    ->get();

        // 最終的にビューに渡す、ネストされた服薬記録のコレクションを初期化
        // 構造:
        // [
        //     'カテゴリ名A' => [
        //         'timing_name_X' => Collection<PostMedicationRecord>,
        //         'timing_name_Y' => Collection<PostMedicationRecord>,
        //     ],
        //     'カテゴリ名B' => [
        //         'timing_name_Z' => Collection<PostMedicationRecord>,
        //     ],
        // ]
        $nestedCategorizedMedicationRecords = new Collection();

        // orderedTimingTagsの順序でループし、データを構築
        foreach ($orderedTimingTags as $timingTag) {
            $categoryName = $timingTag->category_name ?? '未分類';
            $timingName = $timingTag->timing_name ?? '不明なタイミング'; // 詳細タイミング名

            // 現在の TimingTag に一致する PostMedicationRecord をフィルタリング
            $recordsForThisTiming = $post->postMedicationRecords->filter(function ($record) use ($timingTag) {
                // record->timingTag が存在し、かつ timing_tag_id が一致する場合
                return $record->timingTag && $record->timingTag->timing_tag_id === $timingTag->timing_tag_id;
            })
            // この詳細タイミング内の薬を薬の名称でソート
            ->sortBy(function ($record) {
                return $record->medication->medication_name ?? '';
            });

            // 記録がある場合のみ、ネストされたコレクションに追加
            // または、空のタイミングも表示したい場合は常にputする
            if ($recordsForThisTiming->isNotEmpty()) { // 記録があるタイミングのみ表示する場合
            // if (true) { // 全てのタイミングを表示する場合 (記録がなくても)
                // カテゴリが存在しない場合は新しく Collection を作成して追加
                if (!$nestedCategorizedMedicationRecords->has($categoryName)) {
                    $nestedCategorizedMedicationRecords->put($categoryName, new Collection());
                }
                // カテゴリ内のコレクションに、この詳細タイミングの記録を追加
                $nestedCategorizedMedicationRecords->get($categoryName)->put($timingName, $recordsForThisTiming);
            }
        }

        // Bladeで表示するカテゴリのリストを、category_orderでソートして取得
        // DISTINCT ON のエラーを回避するため、get() の後に unique() を適用する
        $displayCategories = TimingTag::whereNotNull('category_name')
            ->orderBy('category_order') // まず category_order でソート
            ->get() // 全てのレコードを取得
            ->unique('category_name') // その後、category_name でユニークなものだけを残す
            ->values(); // コレクションのインデックスをリセット

        // dd($nestedCategorizedMedicationRecords->toArray(), $displayCategories->toArray()); // デバッグが必要な場合のみコメント解除

        return view('posts.show', compact('post', 'nestedCategorizedMedicationRecords', 'displayCategories'));
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

        // ここを修正：taken_dosage を含める
        $selectedMedications = $post->postMedicationRecords->map(function ($pmr) {
            return [
                'medication_id' => $pmr->medication_id,
                'timing_tag_id' => $pmr->timing_tag_id,
                'is_completed' => (bool)$pmr->is_completed,
                'dosage' => $pmr->taken_dosage, // ここを追加
            ];
        })->toArray();
        // dd($selectedMedications); // デバッグ用

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
            'post_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:1000'],
            'all_meds_taken' => ['required', 'boolean'],
            'reason_not_taken' => ['nullable', 'string', 'max:500', 'required_if:all_meds_taken,0'],
            'medications' => ['required', 'array', 'min:1'],
            'medications.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'medications.*.timing_tag_id' => ['required', 'exists:timing_tags,timing_tag_id'],
            'medications.*.is_completed' => ['required', 'boolean'],
            'medications.*.dosage' => 'nullable|string|max:255', // dosageは文字列として受け取る
        ]);

        DB::beginTransaction();
        try {
            $post->update([
                'post_date' => $validatedData['post_date'],
                'all_meds_taken' => $validatedData['all_meds_taken'],
                'reason_not_taken' => $validatedData['reason_not_taken'] ?? null,
                'content' => $validatedData['content'] ?? null,
            ]);

            $post->postMedicationRecords()->delete();

            foreach ($validatedData['medications'] as $medicationRecord) {
                $post->postMedicationRecords()->create([
                    'medication_id' => $medicationRecord['medication_id'],
                    'timing_tag_id' => $medicationRecord['timing_tag_id'],
                    'is_completed' => $medicationRecord['is_completed'],
                    'taken_dosage' => $medicationRecord['dosage'] ?? null, // ここで taken_dosage を保存
                    'taken_at' => now(), // 更新日時を記録
                ]);
            }

            DB::commit();
            return redirect()->route('posts.show', $post->post_id)->with('success', '投稿が正常に更新されました！');
        } catch (\Exception | \Throwable $e) {
            DB::rollBack();
            // 修正箇所: 文字列連結の構文を修正
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
            $post->postMedicationRecords()->delete();
            $post->delete();
            DB::commit();
            return redirect()->route('posts.index')->with('success', '投稿が正常に削除されました！');
        } catch (\Exception | \Throwable $e) {
            DB::rollBack();
            // 修正箇所: 文字列連結の構文を修正
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

        $userId = 1;

        $userExists = User::where('id', $userId)->exists();
        if (!$userExists) {
             Log::error("User ID {$userId} not found for calendar display.");
             return redirect()->route('home')->with('error', 'カレンダー表示に必要なユーザーが見つかりません。');
        }

        $posts = Post::with(['postMedicationRecords.medication', 'postMedicationRecords.timingTag'])
                     ->where('user_id', $userId)
                     ->whereMonth('post_date', $month)
                     ->whereYear('post_date', $year)
                     ->get();

        $medicationStatusByDay = [];
        foreach ($posts as $post) {
            $day = Carbon::parse($post->post_date)->day;

            $status = $post->all_meds_taken ? 'completed' : 'not_completed';
            $details = [
                'status' => $status
            ];

            if ($post->all_meds_taken) {
                $medicationInfo = [];
                foreach ($post->postMedicationRecords as $record) {
                    $medName = $record->medication->medication_name ?? '不明な薬';
                    $timingName = $record->timingTag->timing_name ?? 'タイミングなし';
                    $isCompleted = $record->is_completed ? '完了' : '未完了';
                    // taken_dosage を表示に含める
                    $takenDosage = $record->taken_dosage ? " ({$record->taken_dosage})" : '';
                    $medicationInfo[] = "{$medName}{$takenDosage} ({$timingName}: {$isCompleted})";
                }
                $details['medications'] = $medicationInfo;
            } else {
                $details['reason'] = $post->reason_not_taken;
            }
            $medicationStatusByDay[$day] = $details;
        }

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

        $posts = Post::with([
            'user',
            'postMedicationRecords.medication',
            'postMedicationRecords.timingTag'
        ])
        ->where('user_id', $userId)
        ->whereDate('post_date', $date)
        ->orderBy('post_date', 'desc')
        ->get();

        return view('posts.daily_detail', compact('posts', 'date'));
    }
}

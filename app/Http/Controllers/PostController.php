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
        // バリデーションルールを直接 $request->validate() に記述
        $validatedData = $request->validate([
            'post_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:1000'],
            // all_meds_taken が '0' (false) の場合に reason_not_taken を必須にする
            // それ以外の場合は nullable
            'all_meds_taken' => ['required', 'boolean'],
            'reason_not_taken' => ['nullable', 'string', 'max:500', 'required_if:all_meds_taken,0'],
            'medications' => ['required', 'array', 'min:1'],
            'medications.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'medications.*.timing_tag_id' => ['required', 'exists:timing_tags,timing_tag_id'],
            'medications.*.is_completed' => ['required', 'boolean'],
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

            // medicationRecords をループして保存
            foreach ($validatedData['medications'] as $medicationRecord) {
                $post->postMedicationRecords()->create([
                    'medication_id' => $medicationRecord['medication_id'],
                    'timing_tag_id' => $medicationRecord['timing_tag_id'],
                    'is_completed' => $medicationRecord['is_completed'],
                ]);
            }

            DB::commit();
            return redirect()->route('posts.index')->with('success', '新しい投稿が正常に作成されました！');
        } catch (\Exception | \Throwable $e) { // 広範囲なエラーをキャッチ
            DB::rollBack();
            Log::error('投稿作成エラー: ' . $e->getMessage() . "\n" . $e->getTraceAsString()); // スタックトレースも出力
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
        // リレーションシップを eager load
        $post->load(['user', 'postMedicationRecords.medication', 'postMedicationRecords.timingTag']);

        // 全てのPostMedicationRecordをタイミングタグIDと薬の名前でソート
        // これをコントローラーでソートしておくことで、ビューでの処理がシンプルになる
        $sortedMedicationRecords = $post->postMedicationRecords->sortBy(function($record) {
            $timingId = $record->timingTag ? $record->timingTag->timing_tag_id : PHP_INT_MAX;
            $medName = $record->medication ? $record->medication->medication_name : '';
            // フォーマットして複合キーとして利用 (数値の前に0を埋めて文字列ソートでも正しくなるようにする)
            return sprintf('%010d%s', $timingId, $medName);
        })->values(); // ソート後にコレクションのインデックスをリセット

        // 全ての服用タイミングタグを表示順で取得
        // timing_tag_id が表示順を兼ねていると仮定します。
        // もしTimingTagモデルに別途display_orderなどのカラムがあれば、それを使うべきです。
        $timingTags = TimingTag::orderBy('timing_tag_id')->get();

        return view('posts.show', compact('post', 'sortedMedicationRecords', 'timingTags'));
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

                // ★★★ここを修正します★★★
        // PostMedicationRecord が単一の TimingTag に属するように変更
        $selectedMedications = $post->postMedicationRecords->mapWithKeys(function ($pmr) {
            // PostMedicationRecordにtimingTagリレーションが存在し、それがTimingTagモデルを返すことを想定
            $timingTag = $pmr->timingTag; // 単数形のリレーション名を使用

            return [
                // medication_id をキーにする
                $pmr->medication_id => [
                    'id' => $pmr->medication_id,
                    'name' => $pmr->medication->medication_name,
                    'timing_tag_id' => $pmr->timing_tag_id, // PostMedicationRecord から直接 timing_tag_id を取得
                    'timing_name' => $timingTag ? $timingTag->timing_name : '不明', // timingTag が存在する場合のみ名前を取得
                    'is_completed' => (bool)$pmr->is_completed, // PostMedicationRecord から直接 is_completed を取得
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
        // ★★★修正: バリデーションルールを create/edit フォームの構造に合わせる★★★
        $validatedData = $request->validate([
            'post_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:1000'],
            'all_meds_taken' => ['required', 'boolean'],
            'reason_not_taken' => ['nullable', 'string', 'max:500', 'required_if:all_meds_taken,0'],
            'medications' => ['required', 'array', 'min:1'],
            'medications.*.medication_id' => ['required', 'exists:medications,medication_id'],
            'medications.*.timing_tag_id' => ['required', 'exists:timing_tags,timing_tag_id'],
            'medications.*.is_completed' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $post->update([
                'post_date' => $validatedData['post_date'],
                'all_meds_taken' => $validatedData['all_meds_taken'], // バリデーション済みデータを使用
                'reason_not_taken' => $validatedData['reason_not_taken'] ?? null,
                'content' => $validatedData['content'] ?? null,
            ]);

            // ★★★修正: 既存の PostMedicationRecord を全て削除してから新しいものを作成★★★
            // PostMedicationRecord は Post に hasMany リレーションなので、
            // 関連レコードを全て削除するには postMedicationRecords() メソッドに delete() をチェインする。
            $post->postMedicationRecords()->delete();

            // 新しい PostMedicationRecord をループして作成
            foreach ($validatedData['medications'] as $medicationRecord) {
                $post->postMedicationRecords()->create([
                    'medication_id' => $medicationRecord['medication_id'],
                    'timing_tag_id' => $medicationRecord['timing_tag_id'],
                    'is_completed' => $medicationRecord['is_completed'],
                ]);
            }

            DB::commit();
            return redirect()->route('posts.show', $post->post_id)->with('success', '投稿が正常に更新されました！');
        } catch (\Exception | \Throwable $e) {
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
            // ★★★修正: `timingTags()->detach()` を削除し、関連レコードをまとめて削除★★★
            // PostMedicationRecord は Post に hasMany リレーションなので、
            // 関連レコードを全て削除するには postMedicationRecords() メソッドに delete() をチェインする。
            $post->postMedicationRecords()->delete();
            $post->delete(); // Post自体を削除
            DB::commit();
            return redirect()->route('posts.index')->with('success', '投稿が正常に削除されました！');
        } catch (\Exception | \Throwable $e) {
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
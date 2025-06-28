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
use Illuminate\Support\Collection; // Collection を使用する場合は必ずこの行が必要です
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
        // postMedicationRecords.medication と postMedicationRecords.timingTag は必須
        $post->load(['user', 'postMedicationRecords.medication', 'postMedicationRecords.timingTag']);

        // TimingTag から category_name と category_order のユニークなリストを取得し、表示順を制御
        $displayCategories = TimingTag::select('category_name', 'category_order')
                                       ->distinct()
                                       ->whereNotNull('category_name') // category_name が null のものは除外
                                       ->orderBy('category_order')
                                       ->get();

        // 服薬記録を category_name でグループ化し、各グループ内で薬の名称でソートする
        // ここで、$nestedCategorizedMedicationRecords は、[カテゴリ名 => Collection<PostMedicationRecord>] の構造になる
        $nestedCategorizedMedicationRecords = $post->postMedicationRecords
            ->filter(function ($record) {
                // timingTag リレーションが存在し、かつ category_name が設定されているレコードのみを対象
                return $record->timingTag && $record->timingTag->category_name !== null;
            })
            ->groupBy(function ($record) {
                // timingTag の category_name で直接グループ化
                return $record->timingTag->category_name;
            })
            ->map(function ($medicationRecordsInGroup) {
                // 各カテゴリグループ内の薬を medication_name でソート
                return $medicationRecordsInGroup->sortBy(function ($record) {
                    return $record->medication->medication_name ?? '';
                });
            });

        // dd($nestedCategorizedMedicationRecords->toArray(), $displayCategories->toArray()); // デバッグが必要な場合のみコメント解除
        // dd($displayCategories->toArray()); // $displayCategoriesの中身を直接確認したい場合

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

        $selectedMedications = $post->postMedicationRecords->mapWithKeys(function ($pmr) {
            $timingTag = $pmr->timingTag;

            return [
                $pmr->medication_id => [
                    'id' => $pmr->medication_id,
                    'name' => $pmr->medication->medication_name,
                    'timing_tag_id' => $pmr->timing_tag_id,
                    'timing_name' => $timingTag ? $timingTag->timing_name : '不明',
                    'is_completed' => (bool)$pmr->is_completed,
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
            $post->postMedicationRecords()->delete();
            $post->delete();
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
                    $medicationInfo[] = "{$medName} ({$timingName}: {$isCompleted})";
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

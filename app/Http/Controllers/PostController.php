<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Medication;
use App\Models\TimingTag;
use App\Models\TimingCategory; // TimingCategoryモデルはリレーション経由で使う可能性があるので残しておきます
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
        // カテゴリごとの表示順を定義 (category_orderを使ってユニークなカテゴリを取得)
        // TimingTagテーブルにcategory_nameとcategory_orderが直接存在すると仮定
        $displayCategories = TimingTag::whereNotNull('category_name')
            ->orderBy('category_order')
            ->get()
            ->unique('category_name')
            ->values();

        // createの場合は既存のレコードはないので、old('medications') のみで整形
        $nestedCategorizedMedicationRecords = collect();

        if (old('medications')) {
            $recordsToDisplay = old('medications');
            $recordsCollection = collect($recordsToDisplay);

            foreach ($recordsCollection as $index => $recordData) {
                $timingTag = null;
                $medication = null;

                if (isset($recordData['timing_tag_id'])) {
                    $timingTag = TimingTag::find($recordData['timing_tag_id']);
                }
                if (isset($recordData['medication_id'])) {
                    $medication = Medication::find($recordData['medication_id']);
                }

                // $timingTag->category_name が存在することを前提
                if ($timingTag && $timingTag->category_name) {
                    $categoryName = $timingTag->category_name;
                    $timingName = $timingTag->timing_name;

                    $recordData['medication'] = $medication ? $medication->toArray() : null;
                    $recordData['timing_tag'] = $timingTag->toArray();
                    // timing_tag が category_name を直接持つ場合、categoryリレーションは不要
                    // $recordData['timing_tag']['category'] = $timingTag->category->toArray();

                    $recordData['original_index'] = $index;

                    if (!$nestedCategorizedMedicationRecords->has($categoryName)) {
                        $nestedCategorizedMedicationRecords->put($categoryName, collect());
                    }
                    if (!$nestedCategorizedMedicationRecords->get($categoryName)->has($timingName)) {
                        $nestedCategorizedMedicationRecords->get($categoryName)->put($timingName, collect());
                    }
                    $nestedCategorizedMedicationRecords->get($categoryName)->get($timingName)->push($recordData);
                }
            }
        }

        return view('posts.create', compact('medications', 'timingTags', 'nestedCategorizedMedicationRecords', 'displayCategories'));
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
            'medications.*.taken_dosage' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $post = new Post();
            $post->user_id = Auth::id();
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
                    'taken_dosage' => $medicationRecord['taken_dosage'] ?? null,
                    'taken_at' => now(),
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
        // TimingTagがcategory_nameを直接持っていると仮定し、TimingCategoryリレーションのロードは不要
        $post->load([
            'user',
            'postMedicationRecords.medication',
            'postMedicationRecords.timingTag', // .category は不要と仮定
            'postMedicationRecords' => function ($query) {
                $query->select(['post_id', 'medication_id', 'timing_tag_id', 'is_completed', 'taken_dosage', 'taken_at', 'reason_not_taken']);
            }
        ]);

        // TimingTagをcategory_orderとtiming_tag_idで事前にソートして取得
        // TimingTagがcategory_nameを直接持っていると仮定
        $orderedTimingTags = TimingTag::orderBy('category_order')
                                    ->orderBy('timing_tag_id')
                                    ->get();

        $nestedCategorizedMedicationRecords = new Collection();

        // orderedTimingTagsの順序でループし、データを構築
        foreach ($orderedTimingTags as $timingTag) {
            // TimingTagがcategory_nameを直接持っていると仮定し、そのままアクセス
            $categoryName = $timingTag->category_name ?? '未分類';
            $timingName = $timingTag->timing_name ?? '不明なタイミング';

            // 現在の TimingTag に一致する PostMedicationRecord をフィルタリング
            $recordsForThisTiming = $post->postMedicationRecords->filter(function ($record) use ($timingTag) {
                return $record->timingTag && $record->timingTag->timing_tag_id === $timingTag->timing_tag_id;
            })
            ->sortBy(function ($record) {
                return $record->medication->medication_name ?? '';
            });

            if ($recordsForThisTiming->isNotEmpty()) {
                if (!$nestedCategorizedMedicationRecords->has($categoryName)) {
                    $nestedCategorizedMedicationRecords->put($categoryName, new Collection());
                }
                $nestedCategorizedMedicationRecords->get($categoryName)->put($timingName, $recordsForThisTiming);
            }
        }

        // Bladeで表示するカテゴリのリストを、category_orderでソートして取得
        // TimingTagがcategory_nameとcategory_orderを直接持っていると仮定し、それらを使用
        $displayCategories = TimingTag::whereNotNull('category_name')
            ->orderBy('category_order')
            ->get()
            ->unique('category_name')
            ->values();

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
        // 投稿の所有者でない場合はアクセスを拒否
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $medications = Medication::all();
        $timingTags = TimingTag::all();

        // カテゴリごとの表示順を定義 (category_orderを使ってユニークなカテゴリを取得)
        // TimingTagテーブルにcategory_nameとcategory_orderが直接存在すると仮定
        $displayCategories = TimingTag::whereNotNull('category_name')
            ->orderBy('category_order')
            ->get()
            ->unique('category_name')
            ->values();

        $nestedCategorizedMedicationRecords = collect();

        // old('medications') があればそちらを優先（バリデーションエラー時の再表示用）
        // なければ既存の服薬記録データを使用
        $recordsToDisplay = old('medications', $post->postMedicationRecords->toArray());

        $recordsCollection = collect($recordsToDisplay);

        // showメソッドのロジックに近づけて、カテゴリとタイミングでネストされた構造を構築
        // まず、全てのtimingTagsをカテゴリとタイミングの順でソート
        $orderedTimingTags = TimingTag::orderBy('category_order')
                                    ->orderBy('timing_tag_id')
                                    ->get();

        foreach ($orderedTimingTags as $timingTag) {
            $categoryName = $timingTag->category_name ?? '未分類';
            $timingName = $timingTag->timing_name ?? '不明なタイミング';

            // 現在の TimingTag に一致する PostMedicationRecord をフィルタリング
            // old() または既存のレコードからフィルタリング
            $recordsForThisTiming = $recordsCollection->filter(function ($recordData) use ($timingTag) {
                // $recordData が配列の場合とPostMedicationRecordオブジェクトの場合に対応
                $recordTimingTagId = is_array($recordData) ? ($recordData['timing_tag_id'] ?? null) : ($recordData->timing_tag_id ?? null);
                return $recordTimingTagId === $timingTag->timing_tag_id;
            })
            ->map(function ($recordData, $originalIndex) use ($medications, $timingTags) {
                // old() の場合、リレーションデータがないため、ここで補完
                $medication = Medication::find($recordData['medication_id'] ?? null);
                $timingTag = TimingTag::find($recordData['timing_tag_id'] ?? null);

                return [
                    'medication_id' => $recordData['medication_id'] ?? null,
                    'taken_dosage' => $recordData['taken_dosage'] ?? null,
                    'timing_tag_id' => $recordData['timing_tag_id'] ?? null,
                    'is_completed' => (bool)($recordData['is_completed'] ?? 0),
                    'reason_not_taken' => $recordData['reason_not_taken'] ?? null,
                    'original_index' => $originalIndex, // old() のインデックスを保持
                    'medication' => $medication ? $medication->toArray() : null,
                    'timing_tag' => $timingTag ? $timingTag->toArray() : null,
                ];
            })
            // この詳細タイミング内の薬を薬の名称でソート
            ->sortBy(function ($record) {
                return $record['medication']['medication_name'] ?? '';
            });

            // 記録がある場合のみ、ネストされたコレクションに追加 (showと同じロジック)
            if ($recordsForThisTiming->isNotEmpty()) {
                if (!$nestedCategorizedMedicationRecords->has($categoryName)) {
                    $nestedCategorizedMedicationRecords->put($categoryName, collect());
                }
                $nestedCategorizedMedicationRecords->get($categoryName)->put($timingName, $recordsForThisTiming);
            }
        }
               // ★★★ ここに dd() を追加 ★★★
         dump($medications); // まずは medications を確認
         dump($medications->first()); // もしコレクションなら最初の要素を確認

        return view('posts.edit', compact('post', 'medications', 'timingTags', 'nestedCategorizedMedicationRecords', 'displayCategories'));
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
            'medications.*.taken_dosage' => 'nullable|string|max:255',
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
                    'taken_dosage' => $medicationRecord['taken_dosage'] ?? null,
                    'taken_at' => now(),
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

        $userId = Auth::id();

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
        }  catch (\Exception $e) {
            return redirect()->route('posts.calendar')->with('error', '無効な日付が指定されました。');
        }

        $userId = Auth::id();

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
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
        // ① クエリの開始と eager loading
        // 認証ユーザーのIDで投稿を絞り込む
        $query = Post::with(['user', 'postMedicationRecords.medication', 'postMedicationRecords.timingTag'])
                    ->where('user_id',Auth::id())
                    ->orderBy('post_date', 'desc');

        //内服の有無に対してのフィルタリング
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            if ($filter === 'not_completed') {
                $query->where('all_meds_taken', false);
            }
        }
        //データ取得(pagenation適応)
        $posts = $query->paginate(9);
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

        // 必要なリレーションをロード
        // postMedicationRecords に medication と timingTag をロード
        $post->load(['postMedicationRecords.medication', 'postMedicationRecords.timingTag']);

        $medications = Medication::all(); // medication_id で keyBy しない
        $timingTags = TimingTag::all();   // timing_tag_id で keyBy しない

        // カテゴリごとの表示順を定義
        $displayCategories = TimingTag::whereNotNull('category_name')
            ->orderBy('category_order')
            ->get()
            ->unique('category_name')
            ->values();

        $nestedCategorizedMedicationRecords = collect(); // Blade の既存データ表示用

        // JavaScript に渡すための、フラットな薬の記録配列
        // old() データがあればそれを優先し、なければ既存の投稿の薬の記録を使用
        $jsInitialMedicationRecords = collect(old('medications', []));

        if ($jsInitialMedicationRecords->isEmpty()) {
            // old() データがない場合、既存の投稿の薬の記録を整形して使用
            $recordsToProcessForJs = $post->postMedicationRecords->map(function ($record, $index) {
                // PostMedicationRecord オブジェクトから必要なデータを抽出し、連想配列に変換
                return [
                    'medication_id' => $record->medication_id,
                    'taken_dosage' => $record->taken_dosage,
                    'timing_tag_id' => $record->timing_tag_id,
                    'is_completed' => (bool)$record->is_completed,
                    'reason_not_taken' => $record->reason_not_taken,
                    'original_index' => $index, // 初期表示用のインデックス
                    // Blade の select option で selected 属性を設定するために、medication/timingTag は不要
                ];
            });
            $jsInitialMedicationRecords = $recordsToProcessForJs;
        } else {
            // old() データがある場合、original_index を保持
            $jsInitialMedicationRecords = $jsInitialMedicationRecords->map(function ($recordData, $originalIndex) {
                 $recordData['original_index'] = $originalIndex; // old() のインデックスを保持
                 $recordData['is_completed'] = (bool)($recordData['is_completed'] ?? 0); // 型を確実にboolにする
                 return $recordData;
            });
        }


        // Blade 側で表示するために、ネストされた構造を構築
        // これは create メソッドのロジックと同じで良い
        $orderedTimingTags = TimingTag::orderBy('category_order')
                                    ->orderBy('timing_tag_id')
                                    ->get();

        // old('medications', $post->postMedicationRecords->toArray()); の代わりに $jsInitialMedicationRecords を使う
        // $jsInitialMedicationRecords は既に配列の形になっていることを想定
        foreach ($orderedTimingTags as $timingTag) {
            $categoryName = $timingTag->category_name ?? 'その他';
            $timingName = $timingTag->timing_name ?? '不明なタイミング';
            $currentTimingTagId = $timingTag->timing_tag_id;

            $recordsForThisTiming = $jsInitialMedicationRecords->filter(function ($recordData) use ($currentTimingTagId) {
                return ($recordData['timing_tag_id'] ?? null) == $currentTimingTagId;
            })
            ->map(function ($recordData) use ($medications, $timingTags) {
                // Bladeで利用するmedicationとtiming_tagの詳細情報をここで付与
                $medication = $medications->firstWhere('medication_id', $recordData['medication_id'] ?? null);
                $timingTagInstance = $timingTags->firstWhere('timing_tag_id', $recordData['timing_tag_id'] ?? null);

                $recordData['medication'] = $medication ? $medication->toArray() : null;
                $recordData['timing_tag'] = $timingTagInstance ? $timingTagInstance->toArray() : null;
                // original_index は既に $jsInitialMedicationRecords に含まれているはず
                return $recordData;
            })
            ->sortBy(function ($record) {
                return $record['medication']['medication_name'] ?? '';
            });

            if ($recordsForThisTiming->isNotEmpty()) {
                if (!$nestedCategorizedMedicationRecords->has($categoryName)) {
                    $nestedCategorizedMedicationRecords->put($categoryName, collect());
                }
                $categoryCollection = $nestedCategorizedMedicationRecords->get($categoryName);

                if (!$categoryCollection->has($timingName)) {
                    $categoryCollection->put($timingName, collect());
                }
                $timingCollection = $categoryCollection->get($timingName);

                foreach ($recordsForThisTiming as $record) {
                    $timingCollection->push($record);
                }
                $categoryCollection->put($timingName, $timingCollection);
                $nestedCategorizedMedicationRecords->put($categoryName, $categoryCollection);
            }
        }

        // dd($nestedCategorizedMedicationRecords); // ここで再度ddして、中身を確認しても良い

        return view('posts.edit', compact(
            'post',
            'medications',
            'timingTags',
            'nestedCategorizedMedicationRecords', // Blade のループで使う
            'displayCategories',
            'jsInitialMedicationRecords' // JavaScript に渡す
        ));
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
        // $displayCategories の定義を TimingTag モデルから取得するように変更
        $displayCategories = TimingTag::whereNotNull('category_name')
        ->orderBy('category_order')
        ->get()
        ->unique('category_name')
        ->values();
        
        // 2. 各投稿ごとの nestedCategorizedMedicationRecords の生成
        // $posts コレクションの各Postオブジェクトに、nestedCategorizedMedicationRecords プロパティを追加します。
        $posts->each(function($post_item){ // ① $postsコレクションの各要素を順番に処理
            $nestedCategorizedMedicationRecords = $post_item->postMedicationRecords // ② その投稿に紐づく内服記録を取得
                ->groupBy(function($record){ // ③ 内服記録をカテゴリ（例：朝、昼、夕）でグループ化
                    return $record->timingTag->category_name ?? 'その他';
                })
                ->map(function($categoryGroup) { // ④ 各カテゴリ内の内服記録をタイミング（例：食前、食後）でさらにグループ化
                    return $categoryGroup->groupBy(function ($record){
                        return $record->timingTag->timing_name ?? '不明なタイミング';
                    });
                });
            $post_item->nestedCategorizedMedicationRecords = $nestedCategorizedMedicationRecords; // ⑤ 整形したデータをその投稿オブジェクトに追加
        });
        return view('posts.daily_detail', compact('posts', 'date','displayCategories'));
    }
}
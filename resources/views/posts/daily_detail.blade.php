<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $date->format('Y年m月d日') }} の記録
        </h2>
    </x-slot>

    @php
        // Carbonクラスのuseステートメントは、Bladeの@phpブロック内では使えません。
        // 代わりに、Carbonを呼び出す際にフルパス（\Carbon\Carbon::parse(...)）を使用するか、
        // コントローラーで日付オブジェクトをCarbonインスタンスとして渡すようにします。
        // もしコントローラーで既にCarbonインスタンスとして渡されているなら、
        // この use Carbon\Carbon; は不要です。
        // $date が既に Carbon インスタンスであるため、問題ありません。
        // post->taken_at もCarbonインスタンスであれば、Carbon::parse は不要です。

        // Categoryモデルがないため、displayCategoriesをここで定義します。
        // 実際のアプリケーションでは、TimingTagモデルからカテゴリ情報を取得するか、
        // データベースにCategoryテーブルとモデルを作成し、コントローラーで取得することを推奨します。
        $displayCategories = collect([
            (object)['category_name' => '朝'],
            (object)['category_name' => '昼'],
            (object)['category_name' => '夕'],
            (object)['category_name' => '寝る前'],
            (object)['category_name' => '頓服'],
            (object)['category_name' => 'その他'],
        ]);
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 成功/エラーメッセージの表示 --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
                        <h3 class="text-xl font-bold text-blue-800">{{ $date->format('Y年m月d日') }} の投稿詳細</h3>
                        <a href="{{ route('posts.calendar', ['year' => $date->copy()->subMonth()->year, 'month' => $date->copy()->subMonth()->month]) }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-800 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            カレンダーに戻る
                        </a>
                    </div>

                    @if ($posts->isEmpty())
                        <p class="text-gray-600 text-center text-lg py-10">
                            {{ $date->format('Y年m月d日') }} の投稿はまだありません。
                        </p>
                    @else
                        <div class="space-y-8">
                            @foreach ($posts as $post)
                                <div class="bg-gradient-to-br from-blue-50 to-blue-200 p-7 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                                    <h3 class="text-2xl font-extrabold text-blue-800 mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days mr-3 text-blue-600"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                                        {{ $post->post_date->format('Y年m月d日') }} の内服状況 (ID: {{ $post->post_id }})
                                    </h3>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">ユーザー:</strong> {{ $post->user->name ?? '不明なユーザー' }}</p>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">メモ:</strong> {{ $post->content ?? 'なし' }}</p>

                                    {{-- 個別の投稿カード内に、show.blade.php の服薬状況と個別の服薬記録セクションを統合 --}}
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">服薬状況</h3>
                                        @if ($post->all_meds_taken)
                                            <p class="text-green-600 font-bold flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 mr-1"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                                全ての薬を服用済みです。
                                            </p>
                                        @else
                                            <p class="text-red-600 font-bold flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-1"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                                全ての薬は服用されていません。
                                            </p>
                                            @if ($post->reason_not_taken)
                                                <p class="text-gray-700 mt-2 ml-6">理由: {{ $post->reason_not_taken }}</p>
                                            @endif
                                        @endif
                                    </div>

                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">個別の服薬記録</h3>
                                        @php
                                            // 各$postのpostMedicationRecordsをカテゴリとタイミングでグルーピング
                                            // TimingTagモデルのcategory_nameとtiming_nameプロパティに直接アクセスすることを想定
                                            $nestedCategorizedMedicationRecords = $post->postMedicationRecords
                                                ->groupBy(function($record) {
                                                    // TimingTagが存在しない場合やcategory_nameがない場合のフォールバック
                                                    return $record->timingTag->category_name ?? 'その他';
                                                })
                                                ->map(function($categoryGroup) {
                                                    return $categoryGroup->groupBy(function($record) {
                                                        // TimingTagが存在しない場合やtiming_nameがない場合のフォールバック
                                                        return $record->timingTag->timing_name ?? '不明なタイミング';
                                                    });
                                                });
                                        @endphp

                                        @if ($post->postMedicationRecords->isEmpty())
                                            <p class="text-gray-600">この投稿には薬の記録がありません。</p>
                                        @else
                                            <div class="space-y-6">
                                                @foreach ($displayCategories as $category)
                                                    @if ($nestedCategorizedMedicationRecords->has($category->category_name))
                                                        @php
                                                            $categoryName = $category->category_name;
                                                            $blockClass = "category-block-{$categoryName}";
                                                            $textColorClass = "category-text-{$categoryName}";
                                                            $iconColorClass = "category-icon-{$categoryName}";

                                                            $categoryIcon = '';
                                                            $iconBaseClass = 'w-12 h-12 mr-2';
                                                            switch ($categoryName) {
                                                                case '朝':
                                                                    $categoryIcon = '<img src="' . asset('images/morning.png') . '" alt="朝" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                case '昼':
                                                                    $categoryIcon = '<img src="' . asset('images/noon.png') . '" alt="昼" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                case '夕':
                                                                    $categoryIcon = '<img src="' . asset('images/evening.png') . '" alt="夕" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                case '寝る前':
                                                                    $categoryIcon = '<img src="' . asset('images/night.png') . '" alt="寝る前" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                case '頓服':
                                                                    $categoryIcon = '<img src="' . asset('images/prn.png') . '" alt="頓服" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                case 'その他':
                                                                    $categoryIcon = '<img src="' . asset('images/other.png') . '" alt="その他" class="' . $iconBaseClass . '">';
                                                                    break;
                                                                default:
                                                                    $categoryIcon = '<img src="' . asset('images/default.png') . '" alt="デフォルト" class="' . $iconBaseClass . '">';
                                                                    break;
                                                            }
                                                        @endphp

                                                        <div class="category-block {{ $blockClass }}">
                                                            <h4 class="text-lg font-bold mb-3 flex items-center {{ $textColorClass }}">
                                                                <span class="{{ $iconColorClass }}">{!! $categoryIcon !!}</span>
                                                                {{ $categoryName }}
                                                            </h4>
                                                            <div class="space-y-2">
                                                                @foreach ($nestedCategorizedMedicationRecords->get($categoryName) as $timingName => $recordsInTiming)
                                                                    <div class="ml-4 p-2 rounded-md border border-gray-200 bg-gray-50">
                                                                        <h5 class="font-semibold text-gray-700 text-base mb-1">{{ $timingName }}</h5>
                                                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-800">
                                                                            @foreach ($recordsInTiming as $record)
                                                                                <li class="flex items-center">
                                                                                    <span class="ml-4 text-lg">
                                                                                        @if ($record->medication)
                                                                                            <a href="{{ route('medications.show', ['medication' => $record->medication->medication_id, 'from_post_id' => $post->post_id]) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                                                                                {{ $record->medication->medication_name ?? '不明な薬' }}
                                                                                            </a>
                                                                                        @else
                                                                                            <span class="font-semibold">不明な薬</span>
                                                                                        @endif
                                                                                    </span>

                                                                                    @if ($record->taken_dosage)
                                                                                        <span class="ml-2 text-gray-700 text-lg">{{ $record->taken_dosage }}</span>
                                                                                    @endif

                                                                                    <span class="ml-auto flex items-center">
                                                                                        @if ($record->is_completed)
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle mr-1 text-green-600"><circle cx="12" cy="12" r="10"/></svg>
                                                                                        @else
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-1 text-red-600"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                                                                            @if ($record->reason_not_taken) <span class="ml-1 text-xs text-gray-600">(理由: {{ Str::limit($record->reason_not_taken, 20) }})</span> @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-300">
                                        <a href="{{ route('posts.show', $post->post_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                            詳細
                                        </a>
                                        <a href="{{ route('posts.edit', $post->post_id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                            編集
                                        </a>
                                        <form action="{{ route('posts.destroy', $post->post_id) }}" method="POST" onsubmit="return confirm('本当にこの投稿を削除しますか？ この操作は元に戻せません。');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8 text-center">
                        <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                            トップページに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            新しい投稿を作成
        </h2>
    </x-slot>

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
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">エラーが発生しました！</strong>
                            <span class="block sm:inline">入力内容を確認してください。</span>
                            <ul class="mt-3 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('posts.store') }}" method="POST">
                        @csrf

                        {{-- 投稿日 --}}
                        <div class="mb-4">
                            <label for="post_date" class="block text-sm font-medium text-gray-700">投稿日</label>
                            <input type="date" name="post_date" id="post_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('post_date', date('Y-m-d')) }}" required>
                        </div>

                        {{-- メモ --}}
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">メモ</label>
                            <textarea name="content" id="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('content') }}</textarea>
                        </div>

                        {{-- 全ての薬を服用済みチェックボックス --}}
                        <div class="mb-4 flex items-center">
                            <input type="hidden" name="all_meds_taken" value="0">
                            {{-- old('all_meds_taken', true) はデフォルトでチェック状態にするロジック --}}
                            <input type="checkbox" name="all_meds_taken" id="all_meds_taken" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ old('all_meds_taken', true) ? 'checked' : '' }}>
                            <label for="all_meds_taken" class="ml-2 block text-sm font-medium text-gray-700">全ての薬を服用済み</label>
                        </div>

                        {{-- 服用しなかった理由 (条件付き表示) --}}
                        {{-- old('all_meds_taken', true) はデフォルトでチェック状態なので、最初は非表示 --}}
                        <div class="mb-6" id="reason_not_taken_field" style="{{ old('all_meds_taken', true) ? 'display: none;' : '' }}">
                            <label for="reason_not_taken" class="block text-sm font-medium text-gray-700">服用しなかった理由</label>
                            <textarea name="reason_not_taken" id="reason_not_taken" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('reason_not_taken') }}</textarea>
                        </div>

                        {{-- 動的な薬の服用記録セクション --}}
                        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pills mr-2 text-purple-600"><path d="M12 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/><path d="M2 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/></svg>
                                薬の服用記録
                            </h3>
                            <div id="medication_records_container">
                                {{-- 以前の入力値があればカテゴリとタイミングでグルーピングして再表示 --}}
                                {{-- ★ここから修正：old('medications') の存在チェックを追加し、edit.blade.php と同様の構造にする --}}
                                @if (old('medications'))
                                    <div id="existing_medication_records_wrapper" class="space-y-6">
                                        {{-- old('medications') をカテゴリとタイミングでグルーピングするロジックをここに移植 --}}
                                        {{-- これはController側で $nestedCategorizedMedicationRecords を生成しているはずなので、
                                             そのロジックを old('medications') にも適用するか、
                                             ここで簡易的にループして表示するかを検討します。
                                             ここでは、Controllerが old('medications') を $nestedCategorizedMedicationRecords に変換して
                                             渡している前提で進めます。もしそうでない場合は、Controller側で変換が必要です。
                                        --}}
                                        @foreach ($displayCategories as $category)
                                            @if (isset($nestedCategorizedMedicationRecords) && $nestedCategorizedMedicationRecords->has($category->category_name))
                                                @php
                                                    $categoryName = $category->category_name;
                                                    $categoryIcon = ''; // JS側でアイコンを生成しないため空にする
                                                    $iconBaseClass = 'w-12 h-12 mr-2';
                                                    switch ($categoryName) {
                                                        case '朝': $categoryIcon = '<img src="' . asset('images/morning.png') . '" alt="朝" class="' . $iconBaseClass . '">'; break;
                                                        case '昼': $categoryIcon = '<img src="' . asset('images/noon.png') . '" alt="昼" class="' . $iconBaseClass . '">'; break;
                                                        case '夕': $categoryIcon = '<img src="' . asset('images/evening.png') . '" alt="夕" class="' . $iconBaseClass . '">'; break;
                                                        case '寝る前': $categoryIcon = '<img src="' . asset('images/night.png') . '" alt="寝る前" class="' . $iconBaseClass . '">'; break;
                                                        case '頓服': $categoryIcon = '<img src="' . asset('images/prn.png') . '" alt="頓服" class="' . $iconBaseClass . '">'; break;
                                                        case 'その他': $categoryIcon = '<img src="' . asset('images/other.png') . '" alt="その他" class="' . $iconBaseClass . '">'; break;
                                                        default: $categoryIcon = '<img src="' . asset('images/default.png') . '" alt="デフォルト" class="' . $iconBaseClass . '">'; break;
                                                    }
                                                @endphp
                                                <div class="category-group p-4 border border-gray-300 rounded-md bg-white">
                                                    <h4 class="text-lg font-bold mb-3 flex items-center text-gray-800">
                                                        <span class="text-purple-600">{!! $categoryIcon !!}</span>
                                                        {{ $categoryName }}
                                                    </h4>
                                                    <div class="space-y-4 timing-groups-container">
                                                        @foreach ($nestedCategorizedMedicationRecords->get($categoryName) as $timingName => $recordsInTiming)
                                                            <div class="timing-group p-3 border border-gray-200 rounded-md bg-gray-50">
                                                                <h5 class="font-semibold text-gray-700 text-base mb-2">{{ $timingName }}</h5>
                                                                <div class="medication-record-items-for-timing space-y-3">
                                                                    @foreach ($recordsInTiming as $record)
                                                                        <div class="medication-record-item p-4 border border-gray-200 rounded-md bg-white shadow-sm">
                                                                            <div class="flex justify-end mb-4">
                                                                                <button type="button" class="remove-medication-record text-sm text-red-500 hover:text-red-700 font-bold py-1 px-2 rounded-md border border-red-300 hover:bg-red-50">削除</button>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <label for="medication_id_{{ $record['original_index'] }}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                                                                <select name="medications[{{ $record['original_index'] }}][medication_id]" id="medication_id_{{ $record['original_index'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 medication-select" required>
                                                                                    <option value="">薬を選択してください</option>
                                                                                    @foreach ($medications as $medication)
                                                                                        <option value="{{ $medication->medication_id }}" {{ (isset($record['medication_id']) && $record['medication_id'] == $medication->medication_id) ? 'selected' : '' }}>
                                                                                            {{ $medication->medication_name }} ({{ $medication->dosage }})
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <label for="taken_dosage_{{ $record['original_index'] }}" class="block text-sm font-medium text-gray-700">服用量</label>
                                                                                <select name="medications[{{ $record['original_index'] }}][taken_dosage]" id="taken_dosage_{{ $record['original_index'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                                    <option value="">選択してください</option>
                                                                                    @for ($i = 1; $i <= 10; $i++)
                                                                                        <option value="{{ $i }}錠" {{ (isset($record['taken_dosage']) && $record['taken_dosage'] == $i . '錠') ? 'selected' : '' }}>{{ $i }}錠</option>
                                                                                    @endfor
                                                                                    <option value="その他" {{ (isset($record['taken_dosage']) && $record['taken_dosage'] == 'その他') ? 'selected' : '' }}>その他</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <label for="timing_tag_id_{{ $record['original_index'] }}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                                                                <select name="medications[{{ $record['original_index'] }}][timing_tag_id]" id="timing_tag_id_{{ $record['original_index'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 timing-select" required>
                                                                                    <option value="">タイミングを選択してください</option>
                                                                                    @foreach ($timingTags as $timingTag)
                                                                                        <option value="{{ $timingTag->timing_tag_id }}" {{ (isset($record['timing_tag_id']) && $record['timing_tag_id'] == $timingTag->timing_tag_id) ? 'selected' : '' }}>
                                                                                            {{ $timingTag->timing_name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="flex items-center">
                                                                                <input type="hidden" name="medications[{{ $record['original_index'] }}][is_completed]" value="0">
                                                                                <input type="checkbox" name="medications[{{ $record['original_index'] }}][is_completed]" id="is_completed_{{ $record['original_index'] }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 individual-is-completed-checkbox" value="1" {{ (isset($record['is_completed']) && $record['is_completed']) ? 'checked' : '' }}>
                                                                                <label for="is_completed_{{ $record['original_index'] }}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                                                                            </div>
                                                                            @php
                                                                                $individualReasonStyle = (isset($record['is_completed']) && $record['is_completed']) ? 'display: none;' : 'display: block;';
                                                                            @endphp
                                                                            <div class="mt-2 individual-reason-not-taken-field" style="{{ $individualReasonStyle }}">
                                                                                <label for="reason_not_taken_med_{{ $record['original_index'] }}" class="block text-sm font-medium text-gray-700">服用しなかった理由 (個別)</label>
                                                                                <input type="text"
                                                                                       name="medications[{{ $record['original_index'] }}][reason_not_taken]"
                                                                                       id="reason_not_taken_med_{{ $record['original_index'] }}"
                                                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                                       value="{{ old('medications.'.$record['original_index'].'.reason_not_taken', $record['reason_not_taken'] ?? '') }}">
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div> {{-- .medication-record-items-for-timing --}}
                                                                <button type="button" class="add-medication-record-for-timing inline-flex items-center px-3 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 mt-3"
                                                                    data-timing-tag-id="{{ $recordsInTiming->first()['timing_tag_id'] ?? '' }}"
                                                                    data-timing-name="{{ $timingName }}"
                                                                    data-category-name="{{ $categoryName }}">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                    追加
                                                                </button>
                                                            </div> {{-- .timing-group --}}
                                                        @endforeach
                                                    </div> {{-- .space-y-4 for timings --}}
                                                </div> {{-- .category-group --}}
                                            @endif
                                        @endforeach
                                    </div> {{-- #existing_medication_records_wrapper --}}
                                @else
                                    {{-- 既存の記録がない場合にメッセージと、新規追加されるフォームのラッパー、ボタンを表示 --}}
                                    <p id="no_medication_records_message" class="text-gray-600 mb-4">薬の記録がありません。下のボタンで追加してください。</p>
                                    <div id="new_medication_records_wrapper" class="space-y-3 mb-4">
                                    </div>
                                @endif
                                {{-- 全体で薬を追加するボタン (カテゴリやタイミングを特定せずにどこでも追加できる) --}}
                                <div class="flex justify-center mt-4" id="action_buttons_container"> {{-- ★修正4: action_buttons_container のIDを移動 --}}
                                    <button type="button" id="add_medication_record_overall" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 mt-4">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        薬の記録を新規追加 (カテゴリ未指定)
                                    </button>
                                </div>
                            </div> {{-- #medication_records_container --}}
                        </div> {{-- 動的な薬の服用記録セクション --}}

                        {{-- 送信ボタン --}}
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-lg hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                投稿を作成
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScriptファイルを読み込む前に、必要なデータをグローバル変数として渡す --}}
    <script>
        window.medicationsDataFromBlade = @json($medications->keyBy('medication_id'));
        window.timingTagsFromBlade = @json($timingTags->keyBy('timing_tag_id'));
        window.displayCategoriesFromBlade = @json($displayCategories->keyBy('category_name'));

        @php
            $initialRecordCount = old('medications') ? count(old('medications')) : 0;
        @endphp
        window.medicationRecordIndexFromBlade = {{ $initialRecordCount }};
    </script>
    {{-- Viteを使って app.js と medication-records.js を読み込む --}}
    @vite(['resources/js/app.js', 'resources/js/medication-records.js'])
</x-app-layout>
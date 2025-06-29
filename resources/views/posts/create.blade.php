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
                            <input type="checkbox" name="all_meds_taken" id="all_meds_taken" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ old('all_med_taken', true) ? 'checked' : '' }}>
                            <label for="all_meds_taken" class="ml-2 block text-sm font-medium text-gray-700">全ての薬を服用済み</label>
                        </div>

                        {{-- 服用しなかった理由 (条件付き表示) --}}
                        <div class="mb-6" id="reason_not_taken_field" style="{{ old('all_meds_taken', true) ? 'display: none;' : '' }}">
                            <label for="reason_not_taken" class="block text-sm font-medium text-gray-700">服用しなかった理由</label>
                            <textarea name="reason_not_taken" id="reason_not_taken" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('reason_not_taken') }}</textarea>
                        </div>

                        {{-- 動的な薬の服用記録セクション --}}
                        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pills mr-2 text-purple-600"><path d="M12 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/><path d="M2 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/></svg>
                                薬の服用記録
                            </h3>
                            <div id="medication_records_container">
                                {{-- 以前の入力値があればここに再表示 --}}
                                @if (old('medications'))
                                    @foreach (old('medications') as $index => $oldMedication)
                                        <div class="medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm"> {{-- relative を削除 --}}
                                            {{-- 修正: 削除ボタンをflexコンテナ内に移動し、absoluteを削除 --}}
                                            <div class="flex justify-end"> {{-- ボタンを右端に寄せるためのflexコンテナ --}}
                                                <button type="button" class="remove-medication-record text-sm text-red-500 hover:text-red-700 font-bold py-1 px-2 rounded-md border border-red-300 hover:bg-red-50">削除</button>
                                            </div>
                                            <div class="mb-2">
                                                <label for="medication_id_{{ $index }}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                                <select name="medications[{{ $index }}][medication_id]" id="medication_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 medication-select" required>
                                                    <option value="">薬を選択してください</option>
                                                    @foreach ($medications as $medication)
                                                        <option value="{{ $medication->medication_id }}" {{ (isset($oldMedication['medication_id']) && $oldMedication['medication_id'] == $medication->medication_id) ? 'selected' : '' }}>
                                                            {{ $medication->medication_name }} ({{ $medication->dosage }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            {{-- taken_dosage のドロップダウンフィールド --}}
                                            <div class="mb-2">
                                                <label for="taken_dosage_{{ $index }}" class="block text-sm font-medium text-gray-700">服用量</label>
                                                <select name="medications[{{ $index }}][taken_dosage]" id="taken_dosage_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">選択してください</option>
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}錠" {{ (old('medications.' . $index . '.taken_dosage') == $i . '錠') ? 'selected' : '' }}>{{ $i }}錠</option>
                                                    @endfor
                                                    <option value="その他" {{ (old('medications.' . $index . '.taken_dosage') == 'その他') ? 'selected' : '' }}>その他</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="timing_tag_id_{{ $index }}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                                <select name="medications[{{ $index }}][timing_tag_id]" id="timing_tag_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    <option value="">タイミングを選択してください</option>
                                                    @foreach ($timingTags as $timingTag)
                                                        <option value="{{ $timingTag->timing_tag_id }}" {{ (isset($oldMedication['timing_tag_id']) && $oldMedication['timing_tag_id'] == $timingTag->timing_tag_id) ? 'selected' : '' }}>
                                                            {{ $timingTag->timing_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="hidden" name="medications[{{ $index }}][is_completed]" value="0">
                                                <input type="checkbox" name="medications[{{ $index }}][is_completed]" id="is_completed_{{ $index }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ (isset($oldMedication['is_completed']) && $oldMedication['is_completed']) ? 'checked' : '' }}>
                                                <label for="is_completed_{{ $index }}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" id="add_medication_record" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 mt-4">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                薬の記録を追加
                            </button>
                        </div>

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

    <script>
            window.medicationsDataFromBlade = @json($medications->keyBy('medication_id'));
            window.timingTagsFromBlade = @json($timingTags);
            window.medicationRecordIndexFromBlade = {{ old('medications') ? count(old('medications')) : 0 }};
    </script>
        {{-- Viteを使って app.js と medication-records.js を読み込む --}}
        @vite(['resources/js/app.js', 'resources/js/medication-records.js'])
</x-app-layout>
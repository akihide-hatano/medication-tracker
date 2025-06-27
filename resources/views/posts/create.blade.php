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
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    {{-- バリデーションエラーの表示 --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul class="list-disc list-inside">
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
                            <input type="checkbox" name="all_meds_taken" id="all_meds_taken" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('all_meds_taken') ? 'checked' : '' }}>
                            <label for="all_meds_taken" class="ml-2 block text-sm font-medium text-gray-700">全ての薬を服用済み</label>
                        </div>

                        {{-- 服用しなかった理由 (条件付き表示) --}}
                        <div class="mb-6" id="reason_not_taken_field" style="{{ old('all_meds_taken') ? 'display: none;' : '' }}">
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
                                {{-- 以前の入力値があればここに再表示 --}}
                                @if (old('medications'))
                                    @foreach (old('medications') as $index => $oldMedication)
                                        <div class="medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative">
                                            <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                                            <div class="mb-2">
                                                <label for="medication_id_{{ $index }}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                                <select name="medications[{{ $index }}][medication_id]" id="medication_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    <option value="">薬を選択してください</option>
                                                    {{-- Bladeの変数をJavaScriptに埋め込む --}}
                                                    @foreach ($medications as $medication)
                                                        <option value="{{ $medication->medication_id }}" {{ (isset($oldMedication['medication_id']) && $oldMedication['medication_id'] == $medication->medication_id) ? 'selected' : '' }}>
                                                            {{ $medication->medication_name }} ({{ $medication->dosage }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="timing_tag_id_{{ $index }}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                                <select name="medications[{{ $index }}][timing_tag_id]" id="timing_tag_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    <option value="">タイミングを選択してください</option>
                                                    {{-- Bladeの変数をJavaScriptに埋め込む --}}
                                                    @foreach ($timingTags as $timingTag)
                                                        <option value="{{ $timingTag->timing_tag_id }}" {{ (isset($oldMedication['timing_tag_id']) && $oldMedication['timing_tag_id'] == $timingTag->timing_tag_id) ? 'selected' : '' }}>
                                                            {{ $timingTag->timing_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="medications[{{ $index }}][is_completed]" id="is_completed_{{ $index }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ (isset($oldMedication['is_completed']) && $oldMedication['is_completed']) ? 'checked' : '' }}>
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
        document.addEventListener('DOMContentLoaded', function () {
            const allMedsTakenCheckbox = document.getElementById('all_meds_taken');
            const reasonNotTakenField = document.getElementById('reason_not_taken_field');
            const medicationRecordsContainer = document.getElementById('medication_records_container');
            const addMedicationRecordButton = document.getElementById('add_medication_record');

            let medicationRecordIndex = {{ old('medications') ? count(old('medications')) : 0 }}; // 以前の入力値があれば、その数からインデックスを開始

            // 「全ての薬を服用済み」チェックボックスの表示切り替え関数
            function toggleReasonNotTaken() {
                if (allMedsTakenCheckbox.checked) {
                    reasonNotTakenField.style.display = 'none';
                    reasonNotTakenField.querySelector('textarea').value = ''; // 非表示にする際は中身をクリア
                } else {
                    reasonNotTakenField.style.display = 'block';
                }
            }

            // 新しい薬の記録アイテムを作成する関数
            function createMedicationRecordItem(index, medicationId = '', timingTagId = '', isCompleted = false) {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative';
                itemDiv.innerHTML = `
                    <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                    <div class="mb-2">
                        <label for="medication_id_${index}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                        <select name="medications[${index}][medication_id]" id="medication_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">薬を選択してください</option>
                            {{-- Bladeの変数をJavaScriptに埋め込む --}}
                            @foreach ($medications as $medication)
                                <option value="{{ $medication->medication_id }}">{{ $medication->medication_name }} ({{ $medication->dosage }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="timing_tag_id_${index}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                        <select name="medications[${index}][timing_tag_id]" id="timing_tag_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">タイミングを選択してください</option>
                            {{-- Bladeの変数をJavaScriptに埋め込む --}}
                            @foreach ($timingTags as $timingTag)
                                <option value="{{ $timingTag->timing_tag_id }}">{{ $timingTag->timing_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="medications[${index}][is_completed]" id="is_completed_${index}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_completed_${index}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                    </div>
                `;

                // 以前の入力値があれば、初期値を設定
                if (medicationId) {
                    const medSelect = itemDiv.querySelector(`#medication_id_${index}`);
                    if (medSelect) medSelect.value = medicationId;
                }
                if (timingTagId) {
                    const timingSelect = itemDiv.querySelector(`#timing_tag_id_${index}`);
                    if (timingSelect) timingSelect.value = timingTagId;
                }
                if (isCompleted) {
                    const completedCheckbox = itemDiv.querySelector(`#is_completed_${index}`);
                    if (completedCheckbox) completedCheckbox.checked = true;
                }

                return itemDiv;
            }

            // イベントリスナーの設定
            allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);
            addMedicationRecordButton.addEventListener('click', function () {
                medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex));
                medicationRecordIndex++;
            });

            // 削除ボタンのイベント委譲 (動的に追加される要素に対応)
            medicationRecordsContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-medication-record')) {
                    event.target.closest('.medication-record-item').remove();
                }
            });

            // ページロード時の初期状態を設定 (以前の入力値がある場合に対応)
            toggleReasonNotTaken();

            // 以前の入力値がない場合、デフォルトで1つの薬の記録フォームを追加
            if (!medicationRecordsContainer.querySelector('.medication-record-item')) {
                medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex));
                medicationRecordIndex++;
            }
        });
    </script>
</x-app-layout>
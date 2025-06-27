<x-app-layout> {{-- @extends('layouts.app') の代わりにコンポーネントを使用 --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿を編集
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
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('posts.update', $post->post_id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- 更新処理なので PUT メソッドを使用 --}}

                        {{-- 投稿日 --}}
                        <div class="mb-4">
                            <label for="post_date" class="block text-sm font-medium text-gray-700">投稿日</label>
                            <input type="date" name="post_date" id="post_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('post_date', $post->post_date->format('Y-m-d')) }}" required>
                        </div>

                        {{-- メモ --}}
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">メモ</label>
                            <textarea name="content" id="content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('content', $post->content) }}</textarea>
                        </div>

                        {{-- 全ての薬を服用済みチェックボックス --}}
                        <div class="mb-4 flex items-center">
                            <input type="hidden" name="all_meds_taken" value="0">
                            <input type="checkbox" name="all_meds_taken" id="all_meds_taken" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ old('all_meds_taken', $post->all_meds_taken) ? 'checked' : '' }}>
                            <label for="all_meds_taken" class="ml-2 block text-sm font-medium text-gray-700">全ての薬を服用済み</label>
                        </div>

                        {{-- 服用しなかった理由 (条件付き表示) --}}
                        <div class="mb-6" id="reason_not_taken_field" style="{{ old('all_meds_taken', $post->all_meds_taken) ? 'display: none;' : '' }}">
                            <label for="reason_not_taken" class="block text-sm font-medium text-gray-700">服用しなかった理由</label>
                            <textarea name="reason_not_taken" id="reason_not_taken" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('reason_not_taken', $post->reason_not_taken) }}</textarea>
                        </div>

                        {{-- 動的な薬の服用記録セクション --}}
                        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pills mr-2 text-purple-600"><path d="M12 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/><path d="M2 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/></svg>
                                薬の服用記録
                            </h3>
                            <div id="medication_records_container">
                                @forelse ($selectedMedications as $medicationRecord)
                                    <div class="medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative">
                                        <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                                        <div class="mb-2">
                                            <label for="medication_id_{{ $loop->index }}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                            <select name="medications[{{ $loop->index }}][medication_id]" id="medication_id_{{ $loop->index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                <option value="">薬を選択してください</option>
                                                @foreach ($medications as $medication)
                                                    <option value="{{ $medication->medication_id }}" {{ (old('medications.' . $loop->parent->index . '.medication_id', $medicationRecord['id']) == $medication->medication_id) ? 'selected' : '' }}>
                                                        {{ $medication->medication_name }} ({{ $medication->dosage }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="timing_tag_id_{{ $loop->index }}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                            <select name="medications[{{ $loop->index }}][timing_tag_id]" id="timing_tag_id_{{ $loop->index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                <option value="">タイミングを選択してください</option>
                                                @foreach ($timingTags as $timingTag)
                                                    <option value="{{ $timingTag->timing_tag_id }}" {{ (old('medications.' . $loop->parent->index . '.timing_tag_id', $medicationRecord['timing_tag_id']) == $timingTag->timing_tag_id) ? 'selected' : '' }}>
                                                        {{ $timingTag->timing_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="hidden" name="medications[{{ $loop->index }}][is_completed]" value="0">
                                            <input type="checkbox" name="medications[{{ $loop->index }}][is_completed]" id="is_completed_{{ $loop->index }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ (old('medications.' . $loop->index . '.is_completed', $medicationRecord['is_completed'])) ? 'checked' : '' }}>
                                            <label for="is_completed_{{ $loop->index }}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                                        </div>
                                    </div>
                                @empty
                                    {{-- 既存の薬の記録がない場合でも、少なくとも1つはフォームを表示 --}}
                                    <div class="medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative">
                                        <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                                        <div class="mb-2">
                                            <label for="medication_id_0" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                            <select name="medications[0][medication_id]" id="medication_id_0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                <option value="">薬を選択してください</option>
                                                @foreach ($medications as $medication)
                                                    <option value="{{ $medication->medication_id }}">{{ $medication->medication_name }} ({{ $medication->dosage }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="timing_tag_id_0" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                            <select name="medications[0][timing_tag_id]" id="timing_tag_id_0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                <option value="">タイミングを選択してください</option>
                                                @foreach ($timingTags as $timingTag)
                                                    <option value="{{ $timingTag->timing_tag_id }}">{{ $timingTag->timing_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="hidden" name="medications[0][is_completed]" value="0">
                                            <input type="checkbox" name="medications[0][is_completed]" id="is_completed_0" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1">
                                            <label for="is_completed_0" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" id="add_medication_record" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 mt-4">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                薬の記録を追加
                            </button>
                        </div>

                        {{-- 送信ボタン --}}
                        <div class="flex justify-end space-x-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-lg hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                投稿を更新
                            </button>
                            <a href="{{ route('posts.show', $post->post_id) }}" class="inline-flex items-center px-6 py-3 bg-gray-500 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-lg hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                キャンセル
                            </a>
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

            // 既存のレコードがある場合、その数からインデックスを開始
            let medicationRecordIndex = medicationRecordsContainer.querySelectorAll('.medication-record-item').length;

            // 「全ての薬を服用済み」チェックボックスの表示切り替え関数
            function toggleReasonNotTaken() {
                if (allMedsTakenCheckbox.checked) {
                    reasonNotTakenField.style.display = 'none';
                    const textarea = reasonNotTakenField.querySelector('textarea');
                    if (textarea) textarea.value = ''; // 非表示にする際は中身をクリア
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
                            @foreach ($medications as $medication)
                                <option value="{{ $medication->medication_id }}">{{ $medication->medication_name }} ({{ $medication->dosage }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="timing_tag_id_${index}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                        <select name="medications[${index}][timing_tag_id]" id="timing_tag_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">タイミングを選択してください</option>
                            @foreach ($timingTags as $timingTag)
                                <option value="{{ $timingTag->timing_tag_id }}">{{ $timingTag->timing_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="hidden" name="medications[${index}][is_completed]" value="0">
                        <input type="checkbox" name="medications[${index}][is_completed]" id="is_completed_${index}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1">
                        <label for="is_completed_${index}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                    </div>
                `;

                // 以前の入力値があれば、初期値を設定
                const medSelect = itemDiv.querySelector(`#medication_id_${index}`);
                if (medicationId && medSelect) {
                    medSelect.value = medicationId;
                }
                const timingSelect = itemDiv.querySelector(`#timing_tag_id_${index}`);
                if (timingTagId && timingSelect) {
                    timingSelect.value = timingTagId;
                }
                const completedCheckbox = itemDiv.querySelector(`#is_completed_${index}`);
                if (isCompleted && completedCheckbox) {
                    completedCheckbox.checked = true;
                }

                return itemDiv;
            }

            // イベントリスナーの設定
            allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);
            addMedicationRecordButton.addEventListener('click', function () {
                medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex));
                medicationRecordIndex++;
                updateMedicationRecordIndices(); // 新しいレコード追加後にインデックスを更新
            });

            // 削除ボタンのイベント委譲 (動的に追加される要素に対応)
            medicationRecordsContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-medication-record')) {
                    event.target.closest('.medication-record-item').remove();
                    updateMedicationRecordIndices(); // 削除後にインデックスを更新
                }
            });

            // 薬の記録のインデックスを更新する関数
            function updateMedicationRecordIndices() {
                const records = medicationRecordsContainer.querySelectorAll('.medication-record-item');
                records.forEach((record, index) => {
                    record.querySelectorAll('[name^="medications["]').forEach(input => {
                        const nameAttr = input.getAttribute('name');
                        const newNameAttr = nameAttr.replace(/medications\[\d+\]/, `medications[${index}]`);
                        input.setAttribute('name', newNameAttr);
                    });
                    record.querySelectorAll('[id^="medication_id_"]').forEach(input => {
                        const idAttr = input.getAttribute('id');
                        const newIdAttr = idAttr.replace(/medication_id_\d+/, `medication_id_${index}`);
                        input.setAttribute('id', newIdAttr);
                        // labelのfor属性も更新
                        const labelFor = record.querySelector(`label[for="${idAttr}"]`);
                        if (labelFor) labelFor.setAttribute('for', newIdAttr);
                    });
                     record.querySelectorAll('[id^="timing_tag_id_"]').forEach(input => {
                        const idAttr = input.getAttribute('id');
                        const newIdAttr = idAttr.replace(/timing_tag_id_\d+/, `timing_tag_id_${index}`);
                        input.setAttribute('id', newIdAttr);
                        const labelFor = record.querySelector(`label[for="${idAttr}"]`);
                        if (labelFor) labelFor.setAttribute('for', newIdAttr);
                    });
                    record.querySelectorAll('[id^="is_completed_"]').forEach(input => {
                        const idAttr = input.getAttribute('id');
                        const newIdAttr = idAttr.replace(/is_completed_\d+/, `is_completed_${index}`);
                        input.setAttribute('id', newIdAttr);
                        const labelFor = record.querySelector(`label[for="${idAttr}"]`);
                        if (labelFor) labelFor.setAttribute('for', newIdAttr);
                    });
                });
                medicationRecordIndex = records.length; // 現在の要素数にインデックスを合わせる
            }

            // ページロード時の初期状態を設定
            toggleReasonNotTaken();
        });
    </script>
</x-app-layout>
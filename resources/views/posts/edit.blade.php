<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿編集 ({{ $post->post_date->format('Y年m月d日') }})
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

                    <form action="{{ route('posts.update', $post->post_id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- PUTメソッドで更新リクエストを送信 --}}

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
                                {{-- 既存の薬の記録があればここに表示 --}}
                                {{-- selectedMedications は PostController::edit から渡される --}}
                                @if (isset($selectedMedications) && count($selectedMedications) > 0)
                                    @foreach ($selectedMedications as $index => $record)
                                        <div class="medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative">
                                            <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                                            <div class="mb-2">
                                                <label for="medication_id_{{ $index }}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                                                <select name="medications[{{ $index }}][medication_id]" id="medication_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 medication-select" required>
                                                    <option value="">薬を選択してください</option>
                                                    @foreach ($medications as $medication)
                                                        <option value="{{ $medication->medication_id }}" {{ (old('medications.' . $index . '.medication_id', $record['medication_id']) == $medication->medication_id) ? 'selected' : '' }}>
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
                                                        <option value="{{ $i }}錠" {{ (old('medications.' . $index . '.taken_dosage', $record['taken_dosage']) == $i . '錠') ? 'selected' : '' }}>{{ $i }}錠</option>
                                                    @endfor
                                                    <option value="その他" {{ (old('medications.' . $index . '.taken_dosage', $record['taken_dosage']) == 'その他') ? 'selected' : '' }}>その他</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="timing_tag_id_{{ $index }}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                                                <select name="medications[{{ $index }}][timing_tag_id]" id="timing_tag_id_{{ $index }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    <option value="">タイミングを選択してください</option>
                                                    @foreach ($timingTags as $timingTag)
                                                        <option value="{{ $timingTag->timing_tag_id }}" {{ (old('medications.' . $index . '.timing_tag_id', $record['timing_tag_id']) == $timingTag->timing_tag_id) ? 'selected' : '' }}>
                                                            {{ $timingTag->timing_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="hidden" name="medications[{{ $index }}][is_completed]" value="0">
                                                <input type="checkbox" name="medications[{{ $index }}][is_completed]" id="is_completed_{{ $index }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ (old('medications.' . $index . '.is_completed', $record['is_completed'])) ? 'checked' : '' }}>
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
                        <div class="flex justify-end space-x-4 mt-8">
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

            const medicationsData = @json($medications->keyBy('medication_id'));
            const timingTagsData = @json($timingTags->keyBy('timing_tag_id'));
            const initialMedicationRecords = @json(old('medications', $selectedMedications ?? []));

            let medicationRecordIndex = initialMedicationRecords.length;

            function toggleReasonNotTaken() {
                if (allMedsTakenCheckbox.checked) {
                    reasonNotTakenField.style.display = 'none';
                    const textarea = reasonNotTakenField.querySelector('textarea');
                    if (textarea) textarea.value = '';
                } else {
                    reasonNotTakenField.style.display = 'block';
                }
            }

            function createMedicationRecordItem(index, medicationId = '', timingTagId = '', isCompleted = false, initialTakenDosage = '') {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm relative';
                
                let medicationOptions = '<option value="">薬を選択してください</option>';
                for (const medId in medicationsData) {
                    const medication = medicationsData[medId];
                    medicationOptions += `<option value="${medication.medication_id}">${medication.medication_name} (${medication.dosage})</option>`;
                }

                let timingTagOptions = '<option value="">タイミングを選択してください</option>';
                for (const tagId in timingTagsData) { // JSのfor文
                    const timingTag = timingTagsData[tagId];
                    timingTagOptions += `<option value="${timingTag.timing_tag_id}">${timingTag.timing_name}</option>`;
                }

                let takenDosageOptions = '<option value="">選択してください</option>';
                for (let i = 1; i <= 10; i++) { // JavaScriptのfor文
                    takenDosageOptions += `<option value="${i}錠">${i}錠</option>`;
                }
                takenDosageOptions += `<option value="その他">その他</option>`;


                itemDiv.innerHTML = `
                    <button type="button" class="remove-medication-record absolute top-2 right-2 text-red-500 hover:text-red-700 text-lg">&times;</button>
                    <div class="mb-2">
                        <label for="medication_id_${index}" class="block text-sm font-medium text-gray-700">薬を選択</label>
                        <select name="medications[${index}][medication_id]" id="medication_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 medication-select" required>
                            ${medicationOptions}
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="taken_dosage_${index}" class="block text-sm font-medium text-gray-700">服用量</label>
                        <select name="medications[${index}][taken_dosage]" id="taken_dosage_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            ${takenDosageOptions}
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="timing_tag_id_${index}" class="block text-sm font-medium text-gray-700">服用タイミング</label>
                        <select name="medications[${index}][timing_tag_id]" id="timing_tag_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            ${timingTagOptions}
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="hidden" name="medications[${index}][is_completed]" value="0">
                        <input type="checkbox" name="medications[${index}][is_completed]" id="is_completed_${index}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1">
                        <label for="is_completed_${index}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
                    </div>
                `;

                const medSelect = itemDiv.querySelector(`#medication_id_${index}`);
                const takenDosageSelect = itemDiv.querySelector(`#taken_dosage_${index}`);
                const timingSelect = itemDiv.querySelector(`#timing_tag_id_${index}`);
                const completedCheckbox = itemDiv.querySelector(`#is_completed_${index}`);

                if (medicationId && medSelect) {
                    medSelect.value = medicationId;
                }
                if (timingTagId && timingSelect) {
                    timingSelect.value = timingTagId;
                }
                if (isCompleted && completedCheckbox) {
                    completedCheckbox.checked = true;
                }

                if (initialTakenDosage && takenDosageSelect) {
                    takenDosageSelect.value = initialTakenDosage;
                }

                // 薬の選択が変更されても、taken_dosage を自動設定しない
                // ユーザーが手動で選択するようにする
                // medSelect.addEventListener('change', function() {
                //     // ... 自動設定ロジックは削除 ...
                // });

                return itemDiv;
            }

            allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);
            addMedicationRecordButton.addEventListener('click', function () {
                // initialTakenDosage を空で渡すことで、初期選択なしにする
                medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex, '', '', false, ''));
                medicationRecordIndex++;
            });

            medicationRecordsContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-medication-record')) {
                    event.target.closest('.medication-record-item').remove();
                }
            });

            toggleReasonNotTaken();

            const existingMedicationItems = medicationRecordsContainer.querySelectorAll('.medication-record-item');
            if (existingMedicationItems.length === 0 && medicationRecordIndex === 0) {
                medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex));
                medicationRecordIndex++;
            }

            // ページロード時に既存の medication-select 要素にイベントリスナーを再設定 (自動設定ロジックは削除済み)
            medicationRecordsContainer.querySelectorAll('.medication-record-item').forEach(item => {
                const medSelect = item.querySelector('.medication-select');
                const takenDosageSelect = item.querySelector(`select[id^="taken_dosage_"]`);
                if (medSelect && takenDosageSelect) {
                    // 自動設定ロジックは削除
                    // medSelect.addEventListener('change', function() { /* ... */ });
                    // 初期値セットロジックも、コントローラーから渡された値があれば自動でselectedになるため不要
                    // if (medSelect.value && !takenDosageSelect.value) { /* ... */ }
                }
            });
        });
    </script>
</x-app-layout>
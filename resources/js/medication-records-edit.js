document.addEventListener('DOMContentLoaded', function() {
    const allMedsTakenCheckbox = document.getElementById('all_meds_taken');
    const reasonNotTakenField = document.getElementById('reason_not_taken_field');
    const medicationRecordsContainer = document.getElementById('medication_records_container');
    const actionButtonsContainer = document.getElementById('action_buttons_container'); // ボタン群をまとめるコンテナ

    // Bladeから渡されたデータ（初期値の取得と、新規追加時の選択肢用）
    const medicationsData = window.medicationsDataFromBlade;
    const timingTagsData = window.timingTagsFromBlade;
    const displayCategoriesData = window.displayCategoriesFromBlade;

    // Bladeで計算されたmedicationRecordIndexを初期値として使用
    let medicationRecordIndex = window.medicationRecordIndexFromBlade;

    // 「全ての薬を服用済み」チェックボックスと服用しなかった理由の表示/非表示を切り替える関数
    function toggleReasonNotTaken() {
        if (allMedsTakenCheckbox.checked) {
            reasonNotTakenField.style.display = 'none';
            // 非表示にする際に、服用しなかった理由のテキストエリアをクリア
            const textarea = reasonNotTakenField.querySelector('textarea');
            if (textarea) textarea.value = '';
        } else {
            reasonNotTakenField.style.display = 'block';
        }
    }

    // 初期ロード時に一度実行
    toggleReasonNotTaken();

    // 「全ての薬を服用済み」チェックボックスのイベントリスナー
    allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);

    /**
     * 個別の服用済みチェックボックスと理由フィールドの表示制御ロジックを設定する関数
     * @param {HTMLElement} containerElement - イベントリスナーを設定する対象のコンテナ要素
     */
    function setupIndividualRecordListeners(containerElement) {
        // コンテナ内の全ての「服用した」チェックボックスを取得
        const checkboxes = containerElement.querySelectorAll('.individual-is-completed-checkbox');

        checkboxes.forEach(checkbox => {
            // 各チェックボックスに対応する「服用しなかった理由」フィールドを見つける
            const individualReasonField = checkbox.closest('.medication-record-item').querySelector('.individual-reason-not-taken-field');

            // individualReasonField が存在しない場合は何もしない（hidden inputの場合など）
            if (!individualReasonField) {
                return;
            }

            // 表示・非表示を切り替える関数
            function toggleIndividualReasonField() {
                if (checkbox.checked) { // チェックボックスが「チェックされている」場合
                    individualReasonField.style.display = 'none'; // 理由フィールドを非表示にする
                    const input = individualReasonField.querySelector('input[type="text"]');
                    if (input) input.value = ''; // 非表示にする際に、理由の値をクリア
                } else { // チェックボックスが「チェックされていない」場合
                    individualReasonField.style.display = 'block'; // 理由フィールドを表示する
                }
            }

            // チェックボックスの状態が変更されたら、上記関数を実行
            checkbox.addEventListener('change', toggleIndividualReasonField);

            // ★★★ここが重要★★★
            // ページロード時（既存の記録）や、新しい記録が追加された直後にも、
            // 初期状態を正しく設定するために一度実行します。
            toggleIndividualReasonField();
        });
    }

    /**
     * 新しい薬の記録アイテムのHTML要素を作成する関数。
     * これは「追加」ボタンが押されたときにのみ使用される。
     * @param {number} index - アイテムの一意なインデックス (name属性用)
     * @param {object} initialData - 初期値を含むオブジェクト {medication_id, timing_tag_id, is_completed, taken_dosage, reason_not_taken}
     * @returns {HTMLElement} 作成されたdiv要素
     */
    function createMedicationRecordItem(index, initialData = {}) {
        const itemDiv = document.createElement('div');
        // 新しく追加されたことがわかるように一時的にborderを追加
        itemDiv.className = 'medication-record-item p-4 border border-gray-200 rounded-md bg-white shadow-sm mb-4 border-2 border-green-500 transition-all duration-300';
        console.log(`新しいアイテムを作成しました (index: ${index})`);

        // 薬の選択肢を生成
        let medicationOptions = '<option value="">薬を選択してください</option>';
        for (const medId in medicationsData) {
            const medication = medicationsData[medId];
            const isSelected = (initialData.medication_id == medication.medication_id) ? 'selected' : '';
            medicationOptions += `<option value="${medication.medication_id}" ${isSelected}>${medication.medication_name} (${medication.dosage})</option>`;
        }

        // 服用量の選択肢を生成
        let takenDosageOptions = '<option value="">選択してください</option>';
        for (let i = 1; i <= 10; i++) {
            const isSelected = (initialData.taken_dosage == `${i}錠`) ? 'selected' : '';
            takenDosageOptions += `<option value="${i}錠" ${isSelected}>${i}錠</option>`;
        }
        const isOtherSelected = (initialData.taken_dosage == 'その他') ? 'selected' : '';
        takenDosageOptions += `<option value="その他" ${isOtherSelected}>その他</option>`;

        // 服用タイミングの選択肢を生成
        let timingTagOptions = '<option value="">タイミングを選択してください</option>';
        for (const tagId in timingTagsData) {
            const timingTag = timingTagsData[tagId];
            // initialData.timing_tag_id が undefined または null の場合、何も選択されない
            const isSelected = (initialData.timing_tag_id !== undefined && initialData.timing_tag_id !== null && initialData.timing_tag_id == timingTag.timing_tag_id) ? 'selected' : '';
            timingTagOptions += `<option value="${timingTag.timing_tag_id}" ${isSelected}>${timingTag.timing_name}</option>`;
        }
        const isCompletedChecked = initialData.is_completed ? 'checked' : '';
        const reasonNotTakenValue = initialData.reason_not_taken || '';
        // 新規追加時、is_completedが未定義（またはfalse相当）の場合は理由フィールドを表示
        // 初期データで is_completed が true であれば非表示、そうでなければ表示
        const individualReasonStyle = (initialData.is_completed) ? 'display: none;' : 'display: block;';


        itemDiv.innerHTML = `
            <div class="flex justify-end mb-4">
                <button type="button" class="remove-medication-record text-sm text-red-500 hover:text-red-700 font-bold py-1 px-2 rounded-md border border-red-300 hover:bg-red-50">削除</button>
            </div>
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
                <select name="medications[${index}][timing_tag_id]" id="timing_tag_id_${index}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 timing-select" required>
                    ${timingTagOptions}
                </select>
            </div>
            <div class="flex items-center">
                <input type="hidden" name="medications[${index}][is_completed]" value="0">
                <input type="checkbox" name="medications[${index}][is_completed]" id="is_completed_${index}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 individual-is-completed-checkbox" value="1" ${isCompletedChecked}>
                <label for="is_completed_${index}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
            </div>
            <div class="mt-2 individual-reason-not-taken-field" style="${individualReasonStyle}">
                <label for="reason_not_taken_med_${index}" class="block text-sm font-medium text-gray-700">服用しなかった理由 (個別)</label>
                <input type="text"
                       name="medications[${index}][reason_not_taken]"
                       id="reason_not_taken_med_${index}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       value="${reasonNotTakenValue}">
            </div>
        `;

        // 3秒後に緑のボーダーを消す
        setTimeout(() => {
            itemDiv.classList.remove('border-green-500');
            itemDiv.classList.add('border-gray-200');
        }, 3000);

        return itemDiv;
    }

    // // 「薬の記録を新規追加 (カテゴリ未指定)」ボタンのイベントリスナー
    // // イベント委譲で add_medication_record_overall ボタンを処理
    medicationRecordsContainer.addEventListener('click', function(event) {
        if (event.target.id === 'add_medication_record_overall') {
            const item = createMedicationRecordItem(medicationRecordIndex, {}); // 空のオブジェクトを渡す

            // 「薬の記録がありません」メッセージがあれば非表示にする
            const noRecordsMessage = document.getElementById('no_medication_records_message');
            if (noRecordsMessage) {
                noRecordsMessage.style.display = 'none';
                console.log('「薬の記録がありません」メッセージを非表示にしました。');
            }

            // 新しいフォームは常に actionButtonsContainer の直前に挿入する
            if (actionButtonsContainer) {
                actionButtonsContainer.before(item);
                console.log('新しいアイテムをactionButtonsContainerの直前に挿入しました。');
            } else {
                // 万が一 actionButtonsContainer が見つからなかった場合のフォールバック（medicationRecordsContainer の末尾）
                medicationRecordsContainer.appendChild(item);
                console.warn('actionButtonsContainerが見つからなかったため、medicationRecordsContainerの末尾にアイテムを追加しました。');
            }
            // 新しく追加された要素に対してイベントリスナーを設定
            setupIndividualRecordListeners(item);
            medicationRecordIndex++;
        }
    });

    // カテゴリ別追加ボタン群のイベントリスナー（イベント委譲）
    medicationRecordsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('add-medication-record-by-category')) {
            const categoryName = event.target.dataset.categoryName;
            const timingTagsInCategory = Object.values(timingTagsData).filter(tag => tag.category_name === categoryName);

            if (timingTagsInCategory.length > 0) {
                const firstTimingTag = timingTagsInCategory.sort((a, b) => a.timing_tag_id - b.timing_tag_id)[0];

                const { medicationItemsContainer } = getOrCreateCategoryAndTimingGroups(
                    categoryName,
                    firstTimingTag.timing_name,
                    firstTimingTag.timing_tag_id
                );
                // 既存の「薬の記録がありません」メッセージがあれば非表示にする
                const noRecordsMessage = document.getElementById('no_medication_records_message');
                if (noRecordsMessage) {
                    noRecordsMessage.style.display = 'none';
                    console.log('カテゴリ別追加により「薬の記録がありません」メッセージを非表示にしました。');
                }

                const item = createMedicationRecordItem(medicationRecordIndex, {
                    timing_tag_id: firstTimingTag.timing_tag_id // カテゴリ別追加ではタイミングを自動選択
                });
                medicationItemsContainer.appendChild(item);
                // 新しく追加された要素に対してイベントリスナーを設定
                setupIndividualRecordListeners(item);
                medicationRecordIndex++;
                console.log(`カテゴリ別アイテムを追加しました (カテゴリ: ${categoryName}, インデックス: ${medicationRecordIndex - 1})`);
            } else {
                console.warn(`カテゴリ "${categoryName}" に紐づくタイミングタグが見つかりませんでした。`);
            }
        }
    });

    // 既存および新規の「削除」ボタンのイベントリスナー（イベント委譲）
    medicationRecordsContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-medication-record')) {
            const medicationRecordItem = event.target.closest('.medication-record-item');
            const timingGroupDiv = medicationRecordItem.closest('.timing-group');
            const categoryGroupDiv = medicationRecordItem.closest('.category-group');

            medicationRecordItem.remove(); // 薬の記録アイテムを削除
            console.log('アイテムを削除しました。');

            // 削除後、そのタイミンググループ内に薬の記録が一つもなければ、タイミンググループも削除
            if (timingGroupDiv && timingGroupDiv.querySelectorAll('.medication-record-item').length === 0) {
                timingGroupDiv.remove();
                console.log('空になったタイミンググループを削除しました。');
            }
            // 削除後、そのカテゴリグループ内にタイミンググループが一つもなければ、カテゴリグループも削除
            const existingRecordsWrapper = document.getElementById('existing_medication_records_wrapper');
            if (categoryGroupDiv && categoryGroupDiv.querySelectorAll('.timing-group').length === 0) {
                categoryGroupDiv.remove();
                console.log('空になったカテゴリグループを削除しました。');
                // categoryGroupDiv が削除された場合、それが含まれていた existingRecordsWrapper も空になるかチェック
                if (existingRecordsWrapper && existingRecordsWrapper.querySelectorAll('.category-group').length === 0) {
                    existingRecordsWrapper.remove(); // existingRecordsWrapper も削除
                    console.log('空になったexistingRecordsWrapperを削除しました。');
                }
            }

            // 全ての薬の記録がなくなった場合、「薬の記録がありません」メッセージを表示
            // medicationRecordsContainer の直下に、.medication-record-item（全体追加分）も .category-group（カテゴリ別追加分）もない場合
            const allItems = medicationRecordsContainer.querySelectorAll('.medication-record-item, .category-group');
            const noRecordsMessage = document.getElementById('no_medication_records_message');

            if (allItems.length === 0 && !document.getElementById('existing_medication_records_wrapper')) { 
                if (noRecordsMessage) {
                    noRecordsMessage.style.display = 'block';
                    console.log('「薬の記録がありません」メッセージを再表示しました。');
                } else {
                    // メッセージがない場合は新しく作成して追加
                    const p = document.createElement('p');
                    p.id = 'no_medication_records_message';
                    p.className = 'text-gray-600 mb-4';
                    p.textContent = '薬の記録がありません。下のボタンで追加してください。';

                    // actionButtonsContainer の直前に追加
                    if (actionButtonsContainer) {
                        actionButtonsContainer.before(p);
                        console.log('「薬の記録がありません」メッセージを新しく作成し、actionButtonsContainerの前に挿入しました。');
                    } else {
                        medicationRecordsContainer.appendChild(p);
                        console.warn('「薬の記録がありません」メッセージの挿入先が見つからなかったため、medicationRecordsContainerの末尾に追加しました。');
                    }
                }
            }
        }
    });

    // タイミンググループ内の「追加」ボタンのイベントリスナー（イベント委譲）
    medicationRecordsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('add-medication-record-for-timing')) {
            const timingGroup = event.target.closest('.timing-group');
            const medicationItemsContainer = timingGroup.querySelector('.medication-record-items-for-timing');
            const timingTagId = event.target.dataset.timingTagId;
            const timingName = event.target.dataset.timingName;
            const categoryName = event.target.dataset.categoryName;

            // 既存の「薬の記録がありません」メッセージがあれば非表示にする
            const noRecordsMessage = document.getElementById('no_medication_records_message');
            if (noRecordsMessage) {
                noRecordsMessage.style.display = 'none';
                console.log('タイミング別追加により「薬の記録がありません」メッセージを非表示にしました。');
            }

            const newItem = createMedicationRecordItem(medicationRecordIndex, {
                timing_tag_id: timingTagId // クリックされたタイミングタグのIDを初期値として設定
            });
            medicationItemsContainer.appendChild(newItem);
            // 新しく追加された要素に対してイベントリスナーを設定
            setupIndividualRecordListeners(newItem);
            medicationRecordIndex++;
            console.log(`タイミング別アイテムを追加しました (タイミング: ${timingName}, インデックス: ${medicationRecordIndex - 1})`);
        }
    });

    // ページロード時に既存の記録に対してリスナーを設定する
    const existingRecordsWrapper = document.getElementById('existing_medication_records_wrapper');
    if (existingRecordsWrapper) {
        setupIndividualRecordListeners(existingRecordsWrapper);
    }
});
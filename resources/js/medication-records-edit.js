// resources/js/medication-records-edit.js

document.addEventListener('DOMContentLoaded', function() {
    const allMedsTakenCheckbox = document.getElementById('all_meds_taken');
    const reasonNotTakenField = document.getElementById('reason_not_taken_field');
    const medicationRecordsContainer = document.getElementById('medication_records_container');
    const addMedicationRecordOverallButton = document.getElementById('add_medication_record_overall');

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
     * 新しい薬の記録アイテムのHTML要素を作成する関数。
     * これは「追加」ボタンが押されたときにのみ使用される。
     * @param {number} index - アイテムの一意なインデックス (name属性用)
     * @param {object} initialData - 初期値を含むオブジェクト {medication_id, timing_tag_id, is_completed, taken_dosage, reason_not_taken}
     * @returns {HTMLElement} 作成されたdiv要素
     */
    function createMedicationRecordItem(index, initialData = {}) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'medication-record-item p-4 border border-gray-200 rounded-md bg-white shadow-sm';

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
            const isSelected = (initialData.timing_tag_id == timingTag.timing_tag_id) ? 'selected' : '';
            timingTagOptions += `<option value="${timingTag.timing_tag_id}" ${isSelected}>${timingTag.timing_name}</option>`;
        }

        const isCompletedChecked = initialData.is_completed ? 'checked' : '';
        const reasonNotTakenValue = initialData.reason_not_taken || '';

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
                <input type="checkbox" name="medications[${index}][is_completed]" id="is_completed_${index}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" ${isCompletedChecked}>
                <label for="is_completed_${index}" class="ml-2 block text-sm font-medium text-gray-700">服用した</label>
            </div>
            <input type="hidden" name="medications[${index}][reason_not_taken]" value="${reasonNotTakenValue}">
        `;

        return itemDiv;
    }

    /**
     * カテゴリブロックとタイミンググループのHTML要素を取得または作成する関数。
     * @param {string} categoryName - カテゴリ名
     * @param {string} timingName - タイミング名
     * @param {number} timingTagId - タイミングタグID（新規作成時にボタンに紐づけるため）
     * @returns {Object} { categoryGroupDiv: HTMLElement, timingGroupDiv: HTMLElement, medicationItemsContainer: HTMLElement }
     */
    function getOrCreateCategoryAndTimingGroups(categoryName, timingName, timingTagId) {
        let categoryGroupDiv = medicationRecordsContainer.querySelector(`.category-group[data-category-name="${categoryName}"]`);
        
        // カテゴリグループが存在しない場合は作成
        if (!categoryGroupDiv) {
            categoryGroupDiv = document.createElement('div');
            categoryGroupDiv.className = 'category-group p-4 border border-gray-300 rounded-md bg-white mb-6';
            categoryGroupDiv.setAttribute('data-category-name', categoryName);

            let categoryIconHtml = '';
            let iconBaseClass = 'w-12 h-12 mr-2';
            const imageUrlBase = window.location.origin + '/images/';
            switch (categoryName) {
                case '朝': categoryIconHtml = `<img src="${imageUrlBase}morning.png" alt="朝" class="${iconBaseClass}">`; break;
                case '昼': categoryIconHtml = `<img src="${imageUrlBase}noon.png" alt="昼" class="${iconBaseClass}">`; break;
                case '夕': categoryIconHtml = `<img src="${imageUrlBase}evenig.png" alt="夕" class="${iconBaseClass}">`; break;
                case '寝る前': categoryIconHtml = `<img src="${imageUrlBase}night.png" alt="寝る前" class="${iconBaseClass}">`; break;
                case '頓服': categoryIconHtml = `<img src="${imageUrlBase}prn.png" alt="頓服" class="${iconBaseClass}">`; break;
                case 'その他': categoryIconHtml = `<img src="${imageUrlBase}other.png" alt="その他" class="${iconBaseClass}">`; break;
                default: categoryIconHtml = `<img src="${imageUrlBase}default.png" alt="デフォルト" class="${iconBaseClass}">`; break;
            }

            categoryGroupDiv.innerHTML = `
                <h4 class="text-lg font-bold mb-3 flex items-center text-gray-800">
                    <span class="text-purple-600">${categoryIconHtml}</span>
                    ${categoryName}
                </h4>
                <div class="space-y-4 timing-groups-container"></div>
            `;
            
            // カテゴリの表示順に従って挿入
            let insertedBefore = null;
            // 現在のmedicationRecordsContainer直下のカテゴリグループを取得
            const existingCategoryGroups = Array.from(medicationRecordsContainer.querySelectorAll(':scope > .category-group'));
            
            for (const existingGroup of existingCategoryGroups) {
                const existingCategoryName = existingGroup.dataset.categoryName;
                const existingOrder = displayCategoriesData[existingCategoryName]?.category_order || 999;
                const newOrder = displayCategoriesData[categoryName]?.category_order || 999;

                if (newOrder < existingOrder) {
                    insertedBefore = existingGroup;
                    break;
                }
            }

            if (insertedBefore) {
                medicationRecordsContainer.insertBefore(categoryGroupDiv, insertedBefore);
            } else {
                medicationRecordsContainer.appendChild(categoryGroupDiv);
            }
        }

        let timingGroupsContainer = categoryGroupDiv.querySelector('.timing-groups-container');
        if (!timingGroupsContainer) { // safety check
             timingGroupsContainer = document.createElement('div');
             timingGroupsContainer.className = 'space-y-4 timing-groups-container';
             categoryGroupDiv.appendChild(timingGroupsContainer);
        }

        let timingGroupDiv = timingGroupsContainer.querySelector(`.timing-group[data-timing-name="${timingName}"]`);
        let medicationItemsContainer;

        // タイミンググループが存在しない場合は作成
        if (!timingGroupDiv) {
            timingGroupDiv = document.createElement('div');
            timingGroupDiv.className = 'timing-group p-3 border border-gray-200 rounded-md bg-gray-50';
            timingGroupDiv.setAttribute('data-timing-name', timingName);

            timingGroupDiv.innerHTML = `
                <h5 class="font-semibold text-gray-700 text-base mb-2">${timingName}</h5>
                <div class="medication-record-items-for-timing space-y-3"></div>
                <button type="button" class="add-medication-record-for-timing inline-flex items-center px-3 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 mt-3"
                    data-timing-tag-id="${timingTagId}"
                    data-timing-name="${timingName}"
                    data-category-name="${categoryName}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    追加
                </button>
            `;
            // タイミングタグの順番にソートして挿入
            let insertedTimingBefore = null;
            const existingTimingGroups = Array.from(timingGroupsContainer.querySelectorAll(':scope > .timing-group'));

            for (const existingTimingGroup of existingTimingGroups) {
                const existingTimingName = existingTimingGroup.dataset.timingName;
                const existingTimingTag = Object.values(timingTagsData).find(tag => tag.timing_name === existingTimingName);
                const newTimingTag = Object.values(timingTagsData).find(tag => tag.timing_name === timingName);

                if (existingTimingTag && newTimingTag && newTimingTag.timing_tag_id < existingTimingTag.timing_tag_id) {
                    insertedTimingBefore = existingTimingGroup;
                    break;
                }
            }

            if (insertedTimingBefore) {
                timingGroupsContainer.insertBefore(timingGroupDiv, insertedTimingBefore);
            } else {
                timingGroupsContainer.appendChild(timingGroupDiv);
            }
            
            medicationItemsContainer = timingGroupDiv.querySelector('.medication-record-items-for-timing');
        } else {
            medicationItemsContainer = timingGroupDiv.querySelector('.medication-record-items-for-timing');
        }

        return { categoryGroupDiv, timingGroupDiv, medicationItemsContainer };
    }

    // 「薬の記録を新規追加 (カテゴリ未指定)」ボタンのイベントリスナー
    if (addMedicationRecordOverallButton) {
        addMedicationRecordOverallButton.addEventListener('click', function () {
            // デフォルトのカテゴリとタイミング（例: 最初のカテゴリの最初のタイミング）を取得
            const firstCategory = Object.values(displayCategoriesData).sort((a, b) => (a.category_order || 999) - (b.category_order || 999))[0];
            
            let firstTimingTag = null;
            if (firstCategory) {
                const timingTagsInFirstCategory = Object.values(timingTagsData).filter(tag => tag.category_name === firstCategory.category_name);
                if (timingTagsInFirstCategory.length > 0) {
                    firstTimingTag = timingTagsInFirstCategory.sort((a, b) => a.timing_tag_id - b.timing_tag_id)[0];
                }
            }

            if (firstCategory && firstTimingTag) {
                const { medicationItemsContainer } = getOrCreateCategoryAndTimingGroups(firstCategory.category_name, firstTimingTag.timing_name, firstTimingTag.timing_tag_id);
                const item = createMedicationRecordItem(medicationRecordIndex, {timing_tag_id: firstTimingTag.timing_tag_id});
                medicationItemsContainer.appendChild(item);
                medicationRecordIndex++;
            } else {
                // カテゴリやタイミングが全く定義されていない場合のフォールバック
                const item = createMedicationRecordItem(medicationRecordIndex);
                medicationRecordsContainer.appendChild(item);
                medicationRecordIndex++;
                console.warn('デフォルトのカテゴリとタイミングが見つかりませんでした。通常の薬の記録として追加します。');
            }
        });
    }

    // 既存および新規の「削除」ボタンのイベントリスナー（イベント委譲）
    medicationRecordsContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-medication-record')) {
            const medicationRecordItem = event.target.closest('.medication-record-item');
            const timingGroupDiv = medicationRecordItem.closest('.timing-group');
            const categoryGroupDiv = medicationRecordItem.closest('.category-group');

            medicationRecordItem.remove(); // 薬の記録アイテムを削除

            // 削除後、そのタイミンググループ内に薬の記録が一つもなければ、タイミンググループも削除
            if (timingGroupDiv && timingGroupDiv.querySelectorAll('.medication-record-item').length === 0) {
                timingGroupDiv.remove();
            }
            // 削除後、そのカテゴリグループ内にタイミンググループが一つもなければ、カテゴリグループも削除
            // ただし、「薬の記録を新規追加」ボタンがあるため、medicationRecordsContainer直下のカテゴリグループはすべて消さない
            if (categoryGroupDiv && categoryGroupDiv.querySelectorAll('.timing-group').length === 0) {
                categoryGroupDiv.remove();
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

            const newItem = createMedicationRecordItem(medicationRecordIndex, {
                timing_tag_id: timingTagId // クリックされたタイミングタグのIDを初期値として設定
            });
            medicationItemsContainer.appendChild(newItem);
            medicationRecordIndex++;
        }
    });
});

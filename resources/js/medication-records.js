document.addEventListener('DOMContentLoaded', function() {
    const allMedsTakenCheckbox = document.getElementById('all_meds_taken');
    const reasonNotTakenField = document.getElementById('reason_not_taken_field');
    const medicationRecordsContainer = document.getElementById('medication_records_container');
    const addMedicationRecordOverallButton = document.getElementById('add_medication_record_overall');

    const medicationsData = window.medicationsDataFromBlade || {};
    const timingTagsData = window.timingTagsFromBlade || {};
    const displayCategoriesData = window.displayCategoriesFromBlade || {};

    let medicationRecordIndex = window.medicationRecordIndexFromBlade || 0;

    function toggleReasonNotTaken() {
        if (allMedsTakenCheckbox.checked) {
            reasonNotTakenField.style.display = 'none';
            const textarea = reasonNotTakenField.querySelector('textarea');
            if (textarea) textarea.value = '';
        } else {
            reasonNotTakenField.style.display = 'block';
        }
    }

    toggleReasonNotTaken();
    allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);

    /**
     * 個別の服用済みチェックボックスと理由フィールドの表示制御ロジックを設定する関数
     * @param {HTMLElement} containerElement - イベントリスナーを設定する対象のコンテナ要素
     */
    function setupIndividualRecordListeners(containerElement) {
        const checkboxes = containerElement.querySelectorAll('.individual-is-completed-checkbox');

        checkboxes.forEach(checkbox => {
            const individualReasonField = checkbox.closest('.medication-record-item').querySelector('.individual-reason-not-taken-field');

            if (!individualReasonField) {
                // individualReasonField が見つからない場合は、この要素には連動ロジックが不要か、HTML構造が異なる
                console.warn('individualReasonFieldが見つかりませんでした。HTML構造を確認してください。', checkbox.id);
                return;
            }

            function toggleIndividualReasonField() {
                if (checkbox.checked) {
                    individualReasonField.style.display = 'none';
                    const input = individualReasonField.querySelector('input[type="text"]');
                    if (input) input.value = '';
                } else {
                    individualReasonField.style.display = 'block';
                }
            }

            checkbox.addEventListener('change', toggleIndividualReasonField);
            toggleIndividualReasonField(); // 初期状態を設定
        });
    }

    /**
     * 新しい薬の記録アイテムのHTML要素を作成する関数。
     * @param {number} index - アイテムの一意なインデックス (name属性用)
     * @param {object} initialData - 初期値を含むオブジェクト
     * @returns {HTMLElement} 作成されたdiv要素
     */
    function createMedicationRecordItem(index, initialData = {}) {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'medication-record-item p-4 border border-gray-200 rounded-md bg-white shadow-sm mb-4 border-2 border-green-500 transition-all duration-300';
        console.log(`新しいアイテムを作成しました (index: ${index})`);

        let medicationOptions = '<option value="">薬を選択してください</option>';
        for (const medId in medicationsData) {
            const medication = medicationsData[medId];
            const isSelected = (initialData.medication_id == medication.medication_id) ? 'selected' : '';
            medicationOptions += `<option value="${medication.medication_id}" ${isSelected}>${medication.medication_name} (${medication.dosage})</option>`;
        }

        let takenDosageOptions = '<option value="">選択してください</option>';
        for (let i = 1; i <= 10; i++) {
            const isSelected = (initialData.taken_dosage == `${i}錠`) ? 'selected' : '';
            takenDosageOptions += `<option value="${i}錠" ${isSelected}>${i}錠</option>`;
        }
        const isOtherSelected = (initialData.taken_dosage == 'その他') ? 'selected' : '';
        takenDosageOptions += `<option value="その他" ${isOtherSelected}>その他</option>`;

        let timingTagOptions = '<option value="">タイミングを選択してください</option>';
        Object.values(timingTagsData).forEach(timingTag => {
            const isSelected = (initialData.timing_tag_id !== undefined && initialData.timing_tag_id !== null && initialData.timing_tag_id == timingTag.timing_tag_id) ? 'selected' : '';
            timingTagOptions += `<option value="${timingTag.timing_tag_id}" ${isSelected}>${timingTag.timing_name}</option>`;
        });
        
        const isCompletedChecked = initialData.is_completed ? 'checked' : '';
        const reasonNotTakenValue = initialData.reason_not_taken || '';
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

        setTimeout(() => {
            itemDiv.classList.remove('border-green-500');
            itemDiv.classList.add('border-gray-200');
        }, 3000);

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
        let medicationItemsContainer;

        let existingRecordsWrapper = document.getElementById('existing_medication_records_wrapper');
        let newRecordsWrapper = document.getElementById('new_medication_records_wrapper');

        if (!categoryGroupDiv) {
            categoryGroupDiv = document.createElement('div');
            categoryGroupDiv.className = 'category-group p-4 border border-gray-300 rounded-md bg-white mb-6';
            categoryGroupDiv.setAttribute('data-category-name', categoryName);

            // アイコン表示部分を空にしました（Blade側で提供されることを前提）
            categoryGroupDiv.innerHTML = `
                <h4 class="text-lg font-bold mb-3 flex items-center text-gray-800">
                    <span class="text-purple-600"></span> ${categoryName}
                </h4>
                <div class="space-y-4 timing-groups-container"></div>
            `;

            if (!existingRecordsWrapper) {
                existingRecordsWrapper = document.createElement('div');
                existingRecordsWrapper.id = 'existing_medication_records_wrapper';
                existingRecordsWrapper.className = 'space-y-6';
                
                const noRecordsMessage = document.getElementById('no_medication_records_message');
                if (noRecordsMessage) {
                    noRecordsMessage.after(existingRecordsWrapper);
                    noRecordsMessage.style.display = 'none';
                    console.log('existingRecordsWrapperを作成し、noRecordsMessageの後に挿入しました。');
                } else if (newRecordsWrapper) {
                    newRecordsWrapper.before(existingRecordsWrapper);
                    console.log('existingRecordsWrapperを作成し、newRecordsWrapperの前に挿入しました。');
                } else if (addMedicationRecordOverallButton) {
                    addMedicationRecordOverallButton.before(existingRecordsWrapper);
                    console.log('existingRecordsWrapperを作成し、addMedicationRecordOverallButtonの前に挿入しました。');
                } else {
                    medicationRecordsContainer.appendChild(existingRecordsWrapper);
                    console.log('existingRecordsWrapperを作成し、medicationRecordsContainerの最後に追加しました。');
                }
            }

            let insertedBefore = null;
            const existingCategoryGroups = Array.from(existingRecordsWrapper.querySelectorAll(':scope > .category-group'));
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
                insertedBefore.parentNode.insertBefore(categoryGroupDiv, insertedBefore);
                console.log(`カテゴリグループ "${categoryName}" を既存のカテゴリの前に挿入しました。`);
            } else {
                existingRecordsWrapper.appendChild(categoryGroupDiv);
                console.log(`カテゴリグループ "${categoryName}" を既存のカテゴリの最後に追加しました。`);
            }
        }

        let timingGroupsContainer = categoryGroupDiv.querySelector('.timing-groups-container');
        if (!timingGroupsContainer) { 
             timingGroupsContainer = document.createElement('div');
             timingGroupsContainer.className = 'space-y-4 timing-groups-container';
             categoryGroupDiv.appendChild(timingGroupsContainer);
        }

        let timingGroupDiv = timingGroupsContainer.querySelector(`.timing-group[data-timing-name="${timingName}"]`);
        
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
                console.log(`タイミンググループ "${timingName}" を既存のタイミングの前に挿入しました。`);
            } else {
                timingGroupsContainer.appendChild(timingGroupDiv);
                console.log(`タイミンググループ "${timingName}" を既存のタイミングの最後に追加しました。`);
            }
            medicationItemsContainer = timingGroupDiv.querySelector('.medication-record-items-for-timing');
        } else {
            medicationItemsContainer = timingGroupDiv.querySelector('.medication-record-items-for-timing');
            if (!medicationItemsContainer) {
                medicationItemsContainer = document.createElement('div');
                medicationItemsContainer.className = 'medication-record-items-for-timing space-y-3';
                timingGroupDiv.appendChild(medicationItemsContainer);
            }
        }

        return { categoryGroupDiv, timingGroupDiv, medicationItemsContainer };
    }

    // 「薬の記録を新規追加 (カテゴリ未指定)」ボタンのイベントリスナー
    if (addMedicationRecordOverallButton) {
        addMedicationRecordOverallButton.addEventListener('click', function() {
            const item = createMedicationRecordItem(medicationRecordIndex, {});

            const noRecordsMessage = document.getElementById('no_medication_records_message');
            if (noRecordsMessage) {
                noRecordsMessage.style.display = 'none';
                console.log('「薬の記録がありません」メッセージを非表示にしました。');
            }

            const newRecordsWrapper = document.getElementById('new_medication_records_wrapper');
            if (newRecordsWrapper) {
                newRecordsWrapper.appendChild(item);
                console.log('新しいアイテムをnew_medication_records_wrapperに追加しました。');
            } else if (addMedicationRecordOverallButton) {
                addMedicationRecordOverallButton.before(item);
                console.warn('new_medication_records_wrapperが見つからなかったため、add_medication_record_overallボタンの直前にアイテムを追加しました。');
            } else {
                medicationRecordsContainer.appendChild(item);
                console.warn('挿入先が見つからなかったため、medicationRecordsContainerの末尾にアイテムを追加しました。');
            }
            // 新しく追加された要素に対してイベントリスナーを設定
            setupIndividualRecordListeners(item); // ★この行が重要です★
            medicationRecordIndex++;
        });
    }

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
                const noRecordsMessage = document.getElementById('no_medication_records_message');
                if (noRecordsMessage) {
                    noRecordsMessage.style.display = 'none';
                    console.log('カテゴリ別追加により「薬の記録がありません」メッセージを非表示にしました。');
                }

                const item = createMedicationRecordItem(medicationRecordIndex, {
                    timing_tag_id: firstTimingTag.timing_tag_id
                });
                medicationItemsContainer.appendChild(item);
                // 新しく追加された要素に対してイベントリスナーを設定
                setupIndividualRecordListeners(item); // ★この行が重要です★
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

            medicationRecordItem.remove();
            console.log('アイテムを削除しました。');

            if (timingGroupDiv && timingGroupDiv.querySelectorAll('.medication-record-item').length === 0) {
                timingGroupDiv.remove();
                console.log('空になったタイミンググループを削除しました。');
            }

            const existingRecordsWrapper = document.getElementById('existing_medication_records_wrapper');
            const newRecordsWrapper = document.getElementById('new_medication_records_wrapper');

            if (categoryGroupDiv && categoryGroupDiv.querySelectorAll('.timing-group').length === 0) {
                categoryGroupDiv.remove();
                console.log('空になったカテゴリグループを削除しました。');

                if (existingRecordsWrapper && existingRecordsWrapper.querySelectorAll('.category-group').length === 0) {
                    existingRecordsWrapper.remove();
                    console.log('空になったexistingRecordsWrapperを削除しました。');
                }
            }

            const allItems = medicationRecordsContainer.querySelectorAll('.medication-record-item, .category-group');
            let noRecordsMessage = document.getElementById('no_medication_records_message');

            const isExistingWrapperEmpty = !existingRecordsWrapper || existingRecordsWrapper.querySelectorAll('.category-group').length === 0;
            const isNewWrapperEmpty = !newRecordsWrapper || newRecordsWrapper.querySelectorAll('.medication-record-item').length === 0;

            if (isExistingWrapperEmpty && isNewWrapperEmpty) {
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
                    const currentActionButtonsContainer = document.getElementById('action_buttons_container'); // 現在のactionButtonsContainerを取得
                    if (currentActionButtonsContainer) {
                        currentActionButtonsContainer.before(p);
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

            const noRecordsMessage = document.getElementById('no_medication_records_message');
            if (noRecordsMessage) {
                noRecordsMessage.style.display = 'none';
                console.log('タイミング別追加により「薬の記録がありません」メッセージを非表示にしました。');
            }

            const newItem = createMedicationRecordItem(medicationRecordIndex, {
                timing_tag_id: timingTagId
            });
            medicationItemsContainer.appendChild(newItem);
            // 新しく追加された要素に対してイベントリスナーを設定
            setupIndividualRecordListeners(newItem); // ★この行が重要です★
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
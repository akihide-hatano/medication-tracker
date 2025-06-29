
    const allMedsTakenCheckbox = document.getElementById('all_meds_taken');
    const reasonNotTakenField = document.getElementById('reason_not_taken_field');
    const medicationRecordsContainer = document.getElementById('medication_records_container');
    const addMedicationRecordButton = document.getElementById('add_medication_record');

    // Bladeから渡されるmedicationsDataとtimingTagsをグローバル変数として取得
    let medicationsData = {};
    let timingTags = [];
    let medicationRecordIndex = 0;

    // window オブジェクトからデータを取得
    if (window.medicationsDataFromBlade) {
        medicationsData = window.medicationsDataFromBlade;
        delete window.medicationsDataFromBlade; // 不要になったら削除
    }
    if (window.timingTagsFromBlade) {
        timingTags = window.timingTagsFromBlade;
        delete window.timingTagsFromBlade; // 不要になったら削除
    }
    if (window.medicationRecordIndexFromBlade !== undefined) {
        medicationRecordIndex = window.medicationRecordIndexFromBlade;
        delete window.medicationRecordIndexFromBlade;
    }


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
        // medication-record-item から 'relative' クラスを削除
        itemDiv.className = 'medication-record-item p-4 mb-3 border border-gray-200 rounded-md bg-white shadow-sm';
        
        let medicationOptions = '<option value="">薬を選択してください</option>';
        for (const medId in medicationsData) {
            const medication = medicationsData[medId];
            medicationOptions += `<option value="${medication.medication_id}">${medication.medication_name} (${medication.dosage})</option>`;
        }

        let timingTagOptions = '<option value="">タイミングを選択してください</option>';
        timingTags.forEach(timingTag => {
            timingTagOptions += `<option value="${timingTag.timing_tag_id}">${timingTag.timing_name}</option>`;
        });

        let takenDosageOptions = '<option value="">選択してください</option>';
        for (let i = 1; i <= 10; i++) {
            takenDosageOptions += `<option value="${i}錠">${i}錠</option>`;
        }
        takenDosageOptions += `<option value="その他">その他</option>`;


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

        return itemDiv;
    }

    allMedsTakenCheckbox.addEventListener('change', toggleReasonNotTaken);
    addMedicationRecordButton.addEventListener('click', function () {
        medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex, '', '', false, ''));
        medicationRecordIndex++;
    });

    medicationRecordsContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-medication-record')) {
            event.target.closest('.medication-record-item').remove();
        }
    });

    toggleReasonNotTaken();

    // ページロード時に既存の項目がなければ一つ追加
    const existingMedicationItems = medicationRecordsContainer.querySelectorAll('.medication-record-item');
    if (existingMedicationItems.length === 0 && medicationRecordIndex === 0) {
        medicationRecordsContainer.appendChild(createMedicationRecordItem(medicationRecordIndex));
        medicationRecordIndex++;
    }
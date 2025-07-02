// resources/js/calendar.js

document.addEventListener('DOMContentLoaded', function() {
    const calendarGrid = document.querySelector('.calendar-grid');

    // Bladeから渡されたグローバル変数を使う
    const medicationStatusByDay = window.medicationStatusByDayFromBlade; 
    const year = window.calendarDateYearFromBlade;
    const month = window.calendarDateMonthFromBlade;
    const dailyRecordsRouteTemplate = window.dailyRecordsRouteTemplate; // ルートテンプレートも取得

    const firstDayOfMonth = new Date(year, month - 1, 1);
    const lastDayOfMonth = new Date(year, month, 0);
    const numDaysInMonth = lastDayOfMonth.getDate();

    const firstDayOfWeek = firstDayOfMonth.getDay();

    for (let i = 0; i < firstDayOfWeek; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-cell p-2 h-20 bg-gray-50 rounded-md border border-gray-100 flex items-center justify-center text-gray-400';
        calendarGrid.appendChild(emptyCell);
    }

    for (let day = 1; day <= numDaysInMonth; day++) {
        const dayString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        // JavaScript内でURLを生成する際は、渡されたテンプレート文字列を置換する
        const postDetailUrl = dailyRecordsRouteTemplate.replace('DATE_PLACEHOLDER', dayString);

        const dayCell = document.createElement('div');
        dayCell.className = 'calendar-cell flex flex-col items-center justify-center p-2 h-20 bg-white rounded-md shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200';

        const today = new Date();
        if (year === today.getFullYear() && month === (today.getMonth() + 1) && day === today.getDate()) {
            dayCell.classList.add('bg-blue-100', 'border-blue-400', 'font-bold');
        }

        const cellLink = document.createElement('a');
        cellLink.href = postDetailUrl;
        cellLink.className = 'w-full h-full flex flex-col items-center justify-center no-underline text-current';

        const dayNumber = document.createElement('div');
        dayNumber.className = 'text-lg font-bold text-gray-800';
        dayNumber.textContent = day;
        cellLink.appendChild(dayNumber);

        const statusIndicator = document.createElement('div');
        statusIndicator.className = 'text-2xl mt-1';
        
        const dayData = medicationStatusByDay[day];
        let tooltipText = '';
        let displayMedNames = '';

        if (dayData) {
            if (dayData.status === 'completed') {
                statusIndicator.innerHTML = '<span class="text-green-500">⚪︎</span>';
                tooltipText = '全て服用済み';
                if (dayData.medications && dayData.medications.length > 0) {
                    tooltipText += '\n内服薬: ' + dayData.medications.join(', ');
                    if (dayData.medications.length === 1) {
                        displayMedNames = dayData.medications[0];
                    } else if (dayData.medications.length > 1) {
                        displayMedNames = dayData.medications[0] + '他' + (dayData.medications.length - 1) + '種';
                    }
                }
            } else if (dayData.status === 'not_completed') {
                statusIndicator.innerHTML = '<span class="text-red-500">✕</span>';
                tooltipText = '未完了あり';
                if (dayData.reason) {
                    tooltipText += '\n理由: ' + dayData.reason;
                    displayMedNames = '理由あり';
                } else {
                    displayMedNames = '未完了';
                }
            }
            cellLink.title = tooltipText;
        } else {
            statusIndicator.innerHTML = '<span class="text-gray-300">−</span>';
            tooltipText = '記録なし';
            cellLink.title = tooltipText;
        }
        cellLink.appendChild(statusIndicator);

        const medNamesDisplay = document.createElement('div');
        medNamesDisplay.className = 'text-xs text-gray-600 mt-1 truncate w-full px-1';
        medNamesDisplay.textContent = displayMedNames;
        if (displayMedNames) {
            cellLink.appendChild(medNamesDisplay);
        }

        dayCell.appendChild(cellLink);
        calendarGrid.appendChild(dayCell);
    }

    const totalCells = firstDayOfWeek + numDaysInMonth;
    const remainingCells = (7 - (totalCells % 7)) % 7;
    for (let i = 0; i < remainingCells; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-cell p-2 h-20 bg-gray-50 rounded-md border border-gray-100 flex items-center justify-center text-gray-400';
        calendarGrid.appendChild(emptyCell);
    }
});
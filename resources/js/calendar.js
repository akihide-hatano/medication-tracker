// resources/js/calendar.js

document.addEventListener('DOMContentLoaded', function() {
    // カレンダーグリッド要素を取得します。
    const calendarGrid = document.querySelector('.calendar-grid');

    // Bladeファイルから渡されたグローバル変数から、日ごとの服薬状況データを取得します。
    const medicationStatusByDay = window.medicationStatusByDayFromBlade;
    // Bladeファイルから渡されたグローバル変数から、カレンダーの年を取得します。
    const year = window.calendarDateYearFromBlade;
    // Bladeファイルから渡されたグローバル変数から、カレンダーの月を取得します。
    const month = window.calendarDateMonthFromBlade;
    // Bladeファイルから渡されたグローバル変数から、日ごとの記録詳細ページへのルートテンプレート文字列を取得します。
    const dailyRecordsRouteTemplate = window.dailyRecordsRouteTemplate;

    // 現在の月の最初の日（1日）のDateオブジェクトを作成します。
    const firstDayOfMonth = new Date(year, month - 1, 1);
    // 現在の月の最後の日（最終日）のDateオブジェクトを作成します。
    const lastDayOfMonth = new Date(year, month, 0);
    // 現在の月の総日数を取得します。
    const numDaysInMonth = lastDayOfMonth.getDate();
    // 現在の月の1日が週の何曜日（0:日曜, 1:月曜, ...）にあたるかを取得します。
    const firstDayOfWeek = firstDayOfMonth.getDay();

    // カレンダーの1日より前の空のセル（前月の日付部分）を作成し、カレンダーグリッドに追加します。
    for (let i = 0; i < firstDayOfWeek; i++) {
        const emptyCell = document.createElement('div');
        // ★修正点: h-24 に統一
        emptyCell.className = 'calendar-cell p-2 h-24 bg-gray-50 rounded-md border border-gray-100 flex items-center justify-center text-gray-400';
        calendarGrid.appendChild(emptyCell);
    }

    // 現在の月の1日から最終日までループし、各日のセルを作成します。
    for (let day = 1; day <= numDaysInMonth; day++) {
        // 現在の日付を「YYYY-MM-DD」形式の文字列で作成します。
        const dayString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        // 日ごとの記録詳細ページへのURLテンプレートのプレースホルダーを現在の日付文字列で置換し、最終的なURLを生成します。
        const postDetailUrl = dailyRecordsRouteTemplate.replace('DATE_PLACEHOLDER', dayString);

        // 各日付の表示セルとなるdiv要素を作成します。
        const dayCell = document.createElement('div');
        // ★修正点: h-24 に統一 (h-20 から変更)
        dayCell.className = 'calendar-cell flex flex-col items-center justify-center p-2 h-24 bg-white rounded-md shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200';

        // もし現在の日付が今日の日付と一致する場合、特別なCSSクラスを追加して強調表示します。
        const today = new Date();
        if (year === today.getFullYear() && month === (today.getMonth() + 1) && day === today.getDate()) {
            // 既存の背景色とボーダー色、ホバー色を削除してから、新しい色を追加します
            dayCell.classList.remove('bg-white', 'border-gray-200', 'hover:bg-gray-50');
            dayCell.classList.add('bg-blue-200', 'border-blue-500', 'font-bold', 'hover:bg-blue-300');
        }

        // 日付セル全体をクリック可能にするための<a>要素を作成します。
        const cellLink = document.createElement('a');
        // h-full を削除し、flex-grow を追加
        cellLink.href = postDetailUrl;
        cellLink.className = 'w-full flex flex-col items-center justify-center flex-grow no-underline text-current';

        // 日付の数字を表示するdiv要素を作成します。
        const dayNumber = document.createElement('div');
        dayNumber.className = 'text-lg font-bold text-gray-800';
        dayNumber.textContent = day;
        cellLink.appendChild(dayNumber);

        // 服薬状況を示すアイコンを表示するdiv要素を作成します。
        const statusIndicator = document.createElement('div');
        statusIndicator.className = 'text-2xl mt-1'; // アイコンと日付の間のマージンは維持

        // medicationStatusByDayオブジェクトから、現在の日のデータを取得します。
        const dayData = medicationStatusByDay[day];
        // ツールチップのテキストを初期化します。
        let tooltipText = '';
        // セルに表示する薬の名前やステータスのテキストを初期化します。
        let displayStatusText = '';
        // テキストのデフォルト色をここで定義（未完了の場合など）
        let statusTextColorClass = 'text-gray-600'; // デフォルトは灰色

        // その日のデータが存在するかどうかを確認します。
        if (dayData) {
            // 服用ステータスが「completed」（全て服用済み）の場合の処理です。
            if (dayData.status === 'completed') {
                statusIndicator.innerHTML = '<span class="text-green-500">⚪︎</span>';
                tooltipText = '全て服用済み';
                displayStatusText = '完了';
                statusTextColorClass = 'text-gray-500'; // ★修正点: 完了を赤色に
            }
            // 服用ステータスが「not_completed」（未完了あり）の場合の処理です。
            else if (dayData.status === 'not_completed') {
                statusIndicator.innerHTML = '<span class="text-red-500">✕</span>';
                tooltipText = '未完了あり';
                displayStatusText = '未完了';
                statusTextColorClass = 'text-red-600'; // ★修正点: 未完了は灰色にするか、別途指定
            }
            cellLink.title = tooltipText;
        } else {
            statusIndicator.innerHTML = '<span class="text-gray-300">−</span>';
            tooltipText = '記録なし';
            cellLink.title = tooltipText;
            displayStatusText = '記録なし'; // 記録なしの場合も表示テキストを設定
            statusTextColorClass = 'text-gray-400'; // 記録なしは薄い灰色
        }
        cellLink.appendChild(statusIndicator);

        // 薬の名前やステータスを表示するdiv要素を作成します。
        const statusTextDisplay = document.createElement('div');
        // ★修正点: text-xs に戻し、flex-shrink-0 を追加し、mb-2 を追加。mt-1 は削除したまま
        statusTextDisplay.className = `text-xs ${statusTextColorClass} truncate w-full px-1 flex-shrink-0 mb-2`;
        statusTextDisplay.textContent = displayStatusText;
        if (displayStatusText) { // テキストが存在する場合のみ追加
            cellLink.appendChild(statusTextDisplay);
        }

        // <a>要素を日付セルに追加します。
        dayCell.appendChild(cellLink);
        // 日付セルをカレンダーグリッドに追加します。
        calendarGrid.appendChild(dayCell);
    }

    // カレンダーの総セル数を計算します。
    const totalCells = firstDayOfWeek + numDaysInMonth;
    // カレンダーの最後の行の、月末日より後の空のセル（翌月の日付部分）の数を計算します。
    const remainingCells = (7 - (totalCells % 7)) % 7;
    // 空のセルを作成し、カレンダーグリッドに追加します。
    for (let i = 0; i < remainingCells; i++) {
        const emptyCell = document.createElement('div');
        // ★修正点: h-24 に統一
        emptyCell.className = 'calendar-cell p-2 h-24 bg-gray-50 rounded-md border border-gray-100 flex items-center justify-center text-gray-400';
        calendarGrid.appendChild(emptyCell);
    }
});
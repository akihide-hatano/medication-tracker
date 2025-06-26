<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            服薬カレンダー ({{ $date->format('Y年m月') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('posts.calendar', ['year' => $date->copy()->subMonth()->year, 'month' => $date->copy()->subMonth()->month]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            &lt; 前月
                        </a>
                        <h3 class="text-xl font-bold text-gray-800">{{ $date->format('Y年m月') }}</h3>
                        <a href="{{ route('posts.calendar', ['year' => $date->copy()->addMonth()->year, 'month' => $date->copy()->addMonth()->month]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            次月 &gt;
                        </a>
                    </div>

                    <div class="calendar-grid grid grid-cols-7 gap-1 text-center bg-gray-100 border border-gray-300 rounded-lg p-2 shadow-inner">
                        <div class="text-sm font-semibold text-red-600 py-2">日</div>
                        <div class="text-sm font-semibold text-gray-700 py-2">月</div>
                        <div class="text-sm font-semibold text-gray-700 py-2">火</div>
                        <div class="text-sm font-semibold text-gray-700 py-2">水</div>
                        <div class="text-sm font-semibold text-gray-700 py-2">木</div>
                        <div class="text-sm font-semibold text-gray-700 py-2">金</div>
                        <div class="text-sm font-semibold text-blue-600 py-2">土</div>

                        {{-- カレンダーの日付セルはJavaScriptで生成される --}}
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ route('posts.index') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                            投稿一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarGrid = document.querySelector('.calendar-grid');
            const medicationStatusByDay = @json($medicationStatusByDay);

            const year = {{ $date->year }};
            const month = {{ $date->month }};

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
                // ★★★ここが、新しい posts.daily_records ルートへのURLを生成する部分です★★★
                const postDetailUrl = `{{ route('posts.daily_records', ['date' => 'DATE_PLACEHOLDER']) }}`.replace('DATE_PLACEHOLDER', dayString);
                // ★★★この行が非常に重要です。正確に入力してください★★★

                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-cell flex flex-col items-center justify-center p-2 h-20 bg-white rounded-md shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200';

                const today = new Date();
                if (year === today.getFullYear() && month === (today.getMonth() + 1) && day === today.getDate()) {
                    dayCell.classList.add('bg-blue-100', 'border-blue-400', 'font-bold');
                }

                const cellLink = document.createElement('a');
                // ★★★ここを修正しました：生成した postDetailUrl を使用★★★
                cellLink.href = postDetailUrl;
                // ★★★ここまで修正★★★
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
    </script>
</x-app-layout>
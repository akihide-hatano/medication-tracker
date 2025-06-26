<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            服薬カレンダー ({{ $date->format('Y年m月') }})
        </h2>
    </x-slot>

    {{-- Page Content (メインコンテンツ) --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        {{-- 前月・次月へのリンク --}}
                        <a href="{{ route('posts.calendar', ['year' => $date->copy()->subMonth()->year, 'month' => $date->copy()->subMonth()->month]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            &lt; 前月
                        </a>
                        <h3 class="text-xl font-bold text-gray-800">{{ $date->format('Y年m月') }}</h3>
                        <a href="{{ route('posts.calendar', ['year' => $date->copy()->addMonth()->year, 'month' => $date->copy()->addMonth()->month]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            次月 &gt;
                        </a>
                    </div>

                    <div class="calendar-grid grid grid-cols-7 gap-1 text-center bg-gray-100 border border-gray-300 rounded-lg p-2 shadow-inner">
                        {{-- 曜日ヘッダー --}}
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

    {{-- Lucide Icons の読み込み --}}
    {{-- <script src="https://unpkg.com/lucide@latest/dist/lucide.min.js" defer></script> --}}
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
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-cell flex flex-col items-center justify-center p-2 h-20 bg-white rounded-md shadow-sm border border-gray-200';

                const today = new Date();
                if (year === today.getFullYear() && month === (today.getMonth() + 1) && day === today.getDate()) {
                    dayCell.classList.add('bg-blue-100', 'border-blue-400', 'font-bold');
                }

                const dayNumber = document.createElement('div');
                dayNumber.className = 'text-lg font-bold text-gray-800';
                dayNumber.textContent = day;
                dayCell.appendChild(dayNumber);

                const statusIndicator = document.createElement('div');
                statusIndicator.className = 'text-2xl mt-1';

                if (medicationStatusByDay[day]) {
                    if (medicationStatusByDay[day] === 'completed') {
                        statusIndicator.innerHTML = '<span class="text-green-500">⚪︎</span>';
                        statusIndicator.title = '全て服用済み';
                    } else if (medicationStatusByDay[day] === 'not_completed') {
                        statusIndicator.innerHTML = '<span class="text-red-500">✕</span>';
                        statusIndicator.title = '未完了あり';
                    }
                } else {
                    statusIndicator.innerHTML = '<span class="text-gray-300">−</span>';
                    statusIndicator.title = '記録なし';
                }
                dayCell.appendChild(statusIndicator);

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
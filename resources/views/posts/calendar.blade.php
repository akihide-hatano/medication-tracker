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
     {{-- @json はPHPの変数を安全なJSON形式のJavaScriptオブジェクトに変換します --}}
    <script>
        window.medicationStatusByDayFromBlade = @json($medicationStatusByDay);
        window.calendarDateYearFromBlade = {{ $date->year }};
        window.calendarDateMonthFromBlade = {{ $date->month }};
        // ルートテンプレートも渡すことで、JavaScript側で動的にURLを生成できます
        window.dailyRecordsRouteTemplate = "{{ route('posts.daily_records', ['date' => 'DATE_PLACEHOLDER']) }}";
    </script>
    @vite(['resources/js/app.js', 'resources/js/calendar.js'])
</x-app-layout>
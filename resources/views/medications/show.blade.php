<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            薬の詳細: {{ $medication->medication_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-blue-50 p-6 rounded-lg shadow-md border border-blue-200">
                        <h3 class="text-2xl font-bold text-blue-800 mb-4">{{ $medication->medication_name }}</h3>
                        <p class="text-gray-700 mb-2"><strong>薬のID:</strong> {{ $medication->medication_id }}</p>
                        <p class="text-gray-700 mb-2"><strong>容量:</strong> {{ $medication->dosage }}</p>
                        <p class="text-gray-700 mb-2"><strong>効果:</strong> {{ $medication->effect }}</p>
                        <p class="text-gray-700 mb-2"><strong>副作用:</strong> {{ $medication->side_effect }}</p>
                        <p class="text-gray-700 mb-4"><strong>備考:</strong> {{ $medication->notes }}</p>

                        <div class="mt-6 pt-4 border-t border-gray-300 flex justify-end space-x-3">
                            <a href="{{ route('medications.edit', $medication->medication_id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                編集
                            </a>
                            <form action="{{ route('medications.destroy', $medication->medication_id) }}" method="POST" onsubmit="return confirm('本当にこの薬を削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        {{-- ★★★ここを修正：from_dateがある場合は日付指定の戻るリンクを優先★★★ --}}
                        @if ($from_date)
                            <a href="{{ route('posts.daily_records', ['date' => $from_date]) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                                {{ \Carbon\Carbon::parse($from_date)->format('Y年m月d日') }} の記録に戻る
                            </a>
                        @else
                            <a href="{{ route('medications.index') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                                薬一覧に戻る
                            </a>
                        @endif
                        {{-- ★★★ここまで修正★★★ --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
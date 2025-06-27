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
                        <p class="text-gray-700 mb-2"><strong>副作用:</strong> {{ $medication->side_effects }}</p>
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
                    <div class="mt-8 flex justify-end space-x-4">
                        {{-- from_post_id があれば元の投稿詳細に戻るリンクを表示 --}}
                        @if ($from_post_id)
                            <a href="{{ route('posts.show', $from_post_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left mr-1"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                元の投稿に戻る
                            </a>
                        {{-- from_dateの場合に元のpostsに戻る --}}
                        @elseif ($from_date)
                            <a href="{{ route('posts.daily_records', ['dateString' => $from_date]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-md hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                {{ \Carbon\Carbon::parse($from_date)->format('Y年m月d日') }} の記録に戻る
                            </a>
                        @endif
                        <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-list mr-1"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                            投稿一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
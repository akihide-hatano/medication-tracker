<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            服用タイミング詳細: {{ $timingTag->timing_name }}
        </h2>
    </x-slot>

    {{-- Page Content (メインコンテンツ) --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 成功メッセージの表示 --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-6 bg-gray-50 p-6 rounded-lg shadow-inner">
                        <h3 class="text-2xl font-bold text-indigo-700 mb-4">{{ $timingTag->timing_name }}</h3>
                        <div class="space-y-2 text-lg">
                            <p><strong class="text-gray-700">ID:</strong> {{ $timingTag->timing_tag_id }}</p>
                            <p><strong class="text-gray-700">作成日時:</strong> {{ $timingTag->created_at->format('Y/m/d H:i') }}</p>
                            <p><strong class="text-gray-700">更新日時:</strong> {{ $timingTag->updated_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('timing_tags.edit', $timingTag->timing_tag_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            編集
                        </a>

                        <form action="{{ route('timing_tags.destroy', $timingTag->timing_tag_id) }}" method="POST" onsubmit="return confirm('本当にこのタイミングを削除しますか？ この操作は元に戻せません。');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                削除
                            </button>
                        </form>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('timing_tags.index') }}" class="text-indigo-600 hover:text-indigo-900">
                            服用タイミング一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
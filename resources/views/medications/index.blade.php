<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <div class="flex items-center justify-center gap-2">
            <img class="w-10 h-10" src="/images/prn.png" alt="">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                薬の一覧
            </h2>
        </div>
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

                    {{-- 新しい薬を追加するボタン --}}
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('medications.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            新しい薬を追加
                        </a>
                    </div>

                    @if ($medications->isEmpty())
                        <p class="text-gray-600">まだ薬が登録されていません。</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($medications as $medication)
                                <div class="bg-gray-100 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $medication->medication_name }}</h3>
                                    <p class="text-sm text-gray-600 mb-1"><strong class="text-gray-700">用量:</strong> {{ $medication->dosage ?? '未設定' }}</p>
                                    <p class="text-sm text-gray-600 mb-1"><strong class="text-gray-700">効果:</strong> {{ $medication->effect ?? '未設定' }}</p>
                                    <p class="text-sm text-gray-600 mb-1"><strong class="text-gray-700">副作用:</strong> {{ $medication->side_effects ?? 'なし' }}</p>
                                    <p class="text-sm text-gray-600 mb-4"><strong class="text-gray-700">備考:</strong> {{ $medication->notes ?? 'なし' }}</p>
                                    <div class="flex justify-end items-center space-x-2">
                                       <a href="{{ route('medications.show', ['medication' => $medication->medication_id, 'from_medication_id' => $medication->medication_id]) }}" class="text-blue-600 hover:text-blue-900 text-sm">詳細</a>
                                        <a href="{{ route('medications.edit', $medication->medication_id) }}" class="text-green-600 hover:text-green-900 text-sm">編集</a>
                                        <form action="{{ route('medications.destroy', $medication->medication_id) }}" method="POST" onsubmit="return confirm('本当にこの薬を削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm bg-transparent border-none p-0 cursor-pointer">削除</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-8">
                            {{ $medications->links() }}
                        </div>
                    @endif

                    <div class="mt-6 text-center">
                        <a href="" class="text-indigo-600 hover:text-indigo-900">
                            トップページに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
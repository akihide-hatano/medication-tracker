<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            薬の詳細: {{ $medication->medication_name }}
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
                        <h3 class="text-2xl font-bold text-indigo-700 mb-4">{{ $medication->medication_name }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-lg">
                            <div>
                                <p><strong class="text-gray-700">薬のID:</strong> {{ $medication->medication_id }}</p>
                            </div>
                            <div>
                                <p><strong class="text-gray-700">用量:</strong> {{ $medication->dosage ?? '未設定' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p><strong class="text-gray-700">効果:</strong> {{ $medication->effect ?? '未設定' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p><strong class="text-gray-700">副作用:</strong> {{ $medication->side_effects ?? 'なし' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p><strong class="text-gray-700">備考:</strong> {{ $medication->notes ?? 'なし' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('medications.edit', $medication->medication_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
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

                    <div class="mt-6 text-center">
                        <a href="{{ route('medications.index') }}" class="text-indigo-600 hover:text-indigo-900">
                            薬一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
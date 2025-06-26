<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            服用タイミング編集: {{ $timingTag->timing_name }}
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

                    {{-- バリデーションエラーの表示 --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">エラーが発生しました！</strong>
                            <span class="block sm:inline">入力内容を確認してください。</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('timing_tags.update', $timingTag->timing_tag_id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT') {{-- または @method('PATCH') --}}

                        <div>
                            <label for="timing_name" class="block text-sm font-medium text-gray-700">タイミング名:</label>
                            <input type="text" id="timing_name" name="timing_name" value="{{ old('timing_name', $timingTag->timing_name) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('timing_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                更新
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('timing_tags.show', $timingTag->timing_tag_id) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">
                            詳細に戻る
                        </a>
                        <a href="{{ route('timing_tags.index') }}" class="text-indigo-600 hover:text-indigo-900">
                            服用タイミング一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
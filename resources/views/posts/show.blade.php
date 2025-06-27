<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            投稿詳細 ({{ $post->post_date->format('Y年m月d日') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 成功/エラーメッセージの表示 --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 border-b pb-4">
                        <p class="text-sm text-gray-600">投稿日: <span class="font-bold text-gray-800">{{ $post->post_date->format('Y年m月d日') }}</span></p>
                        <p class="text-sm text-gray-600">投稿者: <span class="font-bold text-gray-800">{{ $post->user->name }}</span></p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">メモ</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $post->content ?? 'なし' }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">服薬状況</h3>
                        @if ($post->all_meds_taken)
                            <p class="text-green-600 font-bold flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 mr-1"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                全ての薬を服用済みです。
                            </p>
                        @else
                            <p class="text-red-600 font-bold flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-1"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                全ての薬は服用されていません。
                            </p>
                            @if ($post->reason_not_taken)
                                <p class="text-gray-700 mt-2 ml-6">理由: {{ $post->reason_not_taken }}</p>
                            @endif
                        @endif
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">個別の服薬記録</h3>
                        @if ($post->postMedicationRecords->isEmpty())
                            <p class="text-gray-600">この投稿には薬の記録がありません。</p>
                        @else
                            <ul class="list-disc list-inside space-y-2">
                                @foreach ($post->postMedicationRecords as $record)
                                    <li class="text-gray-700">
                                        {{-- ★★★ここを修正：薬の名前をリンクにする（from_dateクエリパラメータも追加）★★★ --}}
                                        @if ($record->medication)
                                            <a href="{{ route('medications.show', ['medication' => $record->medication->medication_id, 'from_date' => $post->post_date->format('Y-m-d')]) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $record->medication->medication_name }}
                                            </a>
                                        @else
                                            <span class="font-semibold">不明な薬</span>
                                        @endif
                                        （{{ $record->timingTag->timing_name ?? 'タイミングなし' }}）:
                                        @if ($record->is_completed)
                                            <span class="text-green-600 font-semibold">服用済み</span>
                                            @if ($record->taken_dosage) ({{ $record->taken_dosage }}) @endif
                                        @else
                                            <span class="text-red-600 font-semibold">未服用</span>
                                            @if ($record->reason_not_taken) (理由: {{ $record->reason_not_taken }}) @endif
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-4 mt-8">
                        <a href="{{ route('posts.edit', $post->post_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil mr-1"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="M15 5l4 4"/></svg>
                            編集
                        </a>
                        <form action="{{ route('posts.destroy', $post->post_id) }}" method="POST" onsubmit="return confirm('本当にこの投稿を削除してもよろしいですか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2 mr-1"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                削除
                            </button>
                        </form>
                        <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-lg hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left mr-1"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            一覧に戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
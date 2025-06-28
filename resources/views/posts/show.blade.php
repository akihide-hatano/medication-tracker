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
                        {{-- コントローラーから渡された groupedMedicationRecords が空でないことを確認 --}}
                        @if ($groupedMedicationRecords->isEmpty())
                            <p class="text-gray-600">この投稿には薬の記録がありません。</p>
                        @else
                            <div class="space-y-6"> {{-- 各タイミンググループ間のスペースを確保 --}}
                                {{-- コントローラーから渡された表示順のタイミングタグをループ --}}
                                @foreach ($timingTags as $timingTag)
                                    {{-- 現在のタイミングタグに属する薬の記録があるか確認（コントローラーでグループ化されている） --}}
                                    @if ($groupedMedationRecords->has($timingTag->timing_tag_id))
                                        {{-- ここから背景色と境界線の色を動的に変更 --}}
                                        <div class="p-4 rounded-lg shadow-sm
                                            @if ($timingTag->timing_tag_id == 1) bg-blue-100 border-blue-200
                                            @elseif ($timingTag->timing_tag_id == 2) bg-green-100 border-green-200
                                            @elseif ($timingTag->timing_tag_id == 3) bg-yellow-100 border-yellow-200
                                            @elseif ($timingTag->timing_tag_id == 4) bg-purple-100 border-purple-200
                                            @else bg-gray-100 border-gray-200 @endif">
                                            {{-- h4タグのテキスト色も動的に変更 --}}
                                            <h4 class="text-md font-bold mb-3 flex items-center
                                                @if ($timingTag->timing_tag_id == 1) text-blue-700
                                                @elseif ($timingTag->timing_tag_id == 2) text-green-700
                                                @elseif ($timingTag->timing_tag_id == 3) text-yellow-700
                                                @elseif ($timingTag->timing_tag_id == 4) text-purple-700
                                                @else text-gray-700 @endif">
                                                {{-- SVGアイコンの色もタイミングに合わせて変更 (必要であれば) --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucude-clock mr-2 
                                                    @if ($timingTag->timing_tag_id == 1) text-blue-500
                                                    @elseif ($timingTag->timing_tag_id == 2) text-green-500
                                                    @elseif ($timingTag->timing_tag_id == 3) text-yellow-500
                                                    @elseif ($timingTag->timing_tag_id == 4) text-purple-500
                                                    @else text-gray-500 @endif">
                                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                {{ $timingTag->timing_name }}
                                            </h4>
                                            <ul class="list-none space-y-3">
                                                {{-- グループ化されたコレクションから該当タイミングのレコードを取得してループ --}}
                                                {{-- $groupedMedicationRecords->get($timingTag->timing_tag_id) は既にソートされている --}}
                                                @foreach ($groupedMedicationRecords->get($timingTag->timing_tag_id) as $record)
                                                    <li class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-1 sm:space-y-0 sm:space-x-4">
                                                        <div class="flex items-center flex-grow">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pill mr-2 text-purple-500 flex-shrink-0"><path d="m10.5 20.5 9.5-9.5a4.5 4.5 0 0 0-7.5-7.5L3.5 13.5a4.5 4.5 0 0 0 7.5 7.5Z"/><path d="m14 14 3 3"/><path d="m15 6 3-3"/><path d="m2 22 1-1"/><path d="m19 5 1-1"/></svg>
                                                            <span class="text-gray-700 flex-grow">
                                                                @if ($record->medication && $record->medication->medication_id)
                                                                    <a href="{{ route('medications.show', ['medication' => $record->medication->medication_id, 'from_post_id' => $post->post_id]) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                                                        {{ $record->medication->medication_name ?? '不明な薬' }}
                                                                    </a>
                                                                @else
                                                                    <span class="font-semibold">不明な薬</span>
                                                                @endif
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center sm:justify-end flex-shrink-0">
                                                            @if ($record->is_completed)
                                                                <span class="text-green-600 font-semibold flex items-center text-sm sm:text-base">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 mr-1"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                                                    服用済み
                                                                </span>
                                                            @else
                                                                <span class="text-red-600 font-semibold flex items-center text-sm sm:text-base">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-1"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                                                    未服用
                                                                </span>
                                                                @if ($record->reason_not_taken) <span class="ml-1 text-xs text-gray-600">(理由: {{ Str::limit($record->reason_not_taken, 20) }})</span> @endif
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
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

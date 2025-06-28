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
                        {{-- コントローラーから渡された nestedCategorizedMedicationRecords が空でないことを確認 --}}
                        @if ($nestedCategorizedMedicationRecords->isEmpty())
                            <p class="text-gray-600">この投稿には薬の記録がありません。</p>
                        @else
                            <div class="space-y-6"> {{-- 各カテゴリグループ間のスペースを確保 --}}
                                {{-- コントローラーから渡された表示順のカテゴリをループ --}}
                                @foreach ($displayCategories as $category)
                                    {{-- 現在のカテゴリに属する薬の記録があるか確認 --}}
                                    {{-- nestedCategorizedMedicationRecords はカテゴリ名をキーに持つ --}}
                                    @if ($nestedCategorizedMedicationRecords->has($category->category_name))
                                        @php
                                            $categoryName = $category->category_name;
                                            $blockClass = "category-block-{$categoryName}";
                                            $textColorClass = "category-text-{$categoryName}";
                                            $iconColorClass = "category-icon-{$categoryName}";

                                            // カテゴリごとのアイコン設定
                                            $categoryIcon = '';
                                            switch ($categoryName) {
                                                case '朝':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun-medium mr-2"><circle cx="12" cy="12" r="4"/><path d="M12 4v1"/><path d="M12 19v1"/><path d="M5 12H4"/><path d="M20 12h-1"/><path d="M17.8 6.2l-.7-.7"/><path d="M6.2 17.8l-.7-.7"/><path d="M17.8 17.8l-.7-.7"/><path d="M6.2 6.2l-.7-.7"/></svg>';
                                                    break;
                                                case '昼':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun mr-2"><circle cx="12" cy="12" r="8"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M19.07 4.93l-1.41 1.41"/><path d="M6.34 17.66l-1.41 1.41"/></svg>';
                                                    break;
                                                case '夕':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-sun mr-2"><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M19.07 4.93l-1.41 1.41"/><path d="M6.34 17.66l-1.41 1.41"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M19.07 4.93l-1.41 1.41"/><path d="M6.34 17.66l-1.41 1.41"/></svg>';
                                                    break;
                                                case '寝る前':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon-star mr-2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/><path d="M17.5 1.4L19.1 5 22.6 6.6 19.1 8.2 17.5 11.6 15.9 8.2 12.4 6.6 15.9 5Z"/></svg>';
                                                    break;
                                                case '頓服':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucude-pill mr-2"><path d="m10.5 20.5 9.5-9.5a4.5 4.5 0 0 0-7.5-7.5L3.5 13.5a4.5 4.5 0 0 0 7.5 7.5Z"/><path d="m14 14 3 3"/><path d="m15 6 3-3"/><path d="m2 22 1-1"/><path d="m19 5 1-1"/></svg>';
                                                    break;
                                                case 'その他':
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-horizontal mr-2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>';
                                                    break;
                                                default:
                                                    $categoryIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-help mr-2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>';
                                                    break;
                                            }
                                        @endphp

                                        <div class="category-block {{ $blockClass }}">
                                            <h4 class="text-lg font-bold mb-3 flex items-center {{ $textColorClass }}">
                                                <span class="{{ $iconColorClass }}">{!! $categoryIcon !!}</span>
                                                {{ $categoryName }}
                                            </h4>
                                            {{-- カテゴリ内の全ての服薬記録をここに表示 --}}
                                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-800 ml-4 p-2 rounded-md border border-gray-200 bg-gray-50">
                                                @php
                                                    // 現在のカテゴリの全てのレコードを取得
                                                    $recordsInCurrentCategory = $nestedCategorizedMedicationRecords->get($categoryName);
                                                @endphp

                                                @forelse ($recordsInCurrentCategory as $record)
                                                    <li class="flex items-center">
                                                        {{-- アイコンと薬の情報を表示 --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pill mr-2 text-purple-500 flex-shrink-0"><path d="m10.5 20.5 9.5-9.5a4.5 4.5 0 0 0-7.5-7.5L3.5 13.5a4.5 4.5 0 0 0 7.5 7.5Z"/><path d="m14 14 3 3"/><path d="m15 6 3-3"/><path d="m2 22 1-1"/><path d="m19 5 1-1"/></svg>
                                                        <span>
                                                            @if ($record->medication)
                                                                <a href="{{ route('medications.show', ['medication' => $record->medication->medication_id, 'from_post_id' => $post->post_id]) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                                                    {{ $record->medication->medication_name ?? '不明な薬' }}
                                                                </a>
                                                            @else
                                                                <span class="font-semibold">不明な薬</span>
                                                            @endif
                                                        </span>
                                                        {{-- 服用状況の表示 --}}
                                                        <span class="ml-auto flex items-center">
                                                            @if ($record->is_completed)
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 mr-1 text-green-600"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                                                <span class="text-green-600">服用済み</span>
                                                            @else
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-1 text-red-600"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                                                <span class="text-red-600">未服用</span>
                                                                @if ($record->reason_not_taken) <span class="ml-1 text-xs text-gray-600">(理由: {{ Str::limit($record->reason_not_taken, 20) }})</span> @endif
                                                            @endif
                                                        </span>
                                                    </li>
                                                @empty
                                                    <li>このカテゴリには薬の記録がありません。</li>
                                                @endforelse
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

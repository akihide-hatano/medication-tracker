<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-center gap-2">
            <img class="w-10 h-10" src="{{ asset('images/memo.png') }}" alt="メモのアイコン">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                投稿一覧
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            {{-- 全ての投稿を表示するボタン --}}
                            <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                全て表示
                            </a>
                            {{-- 内服未完了の投稿のみを表示するボタン --}}
                            <a href="{{ route('posts.index', ['filter' => 'not_completed']) }}" class="inline-flex items-center px-4 py-2 bg-red-200 border border-transparent rounded-md font-semibold text-xs text-red-800 uppercase tracking-widest hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                内服未完了のみ
                            </a>
                        </div>
                        {{-- 新しい投稿を追加するボタン --}}
                        <a href="{{ route('posts.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-lg hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            新しい投稿を追加
                        </a>
                    </div>

                    @if ($posts->isEmpty())
                        <p class="text-gray-600 text-center text-lg py-10">まだ投稿がありません。新しい投稿を作成してみましょう。</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach ($posts as $post)
                                {{-- ★★★ここから修正：服薬状況に応じて背景色を変更★★★ --}}
                                @php
                                    $cardClasses = 'p-7 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1';
                                    if ($post->all_meds_taken) {
                                        // 全て服用済みの場合：青系のグラデーション（元々の色）
                                        $cardClasses .= ' bg-gradient-to-br from-blue-50 to-blue-200';
                                    } else {
                                        // 未完了がある場合：赤系のグラデーション（薄い赤）
                                        $cardClasses .= ' bg-gradient-to-br from-red-50 to-red-100'; // to-red-100でより薄く
                                    }
                                @endphp
                                <div class="{{ $cardClasses }}">
                                    <h3 class="text-2xl font-extrabold {{ $post->all_meds_taken ? 'text-blue-800' : 'text-red-800' }} mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days mr-3 {{ $post->all_meds_taken ? 'text-blue-600' : 'text-red-600' }}"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                                        {{ $post->post_date->format('Y年m月d日') }}
                                    </h3>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">ユーザー:</strong> {{ $post->user->name ?? '不明なユーザー' }}</p>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">メモ:</strong> {{ Str::limit($post->content, 100) ?? 'なし' }}</p>
                                    <p class="text-sm text-gray-700 mb-2 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pill mr-2 {{ $post->all_meds_taken ? 'text-blue-600' : 'text-red-600' }}"><path d="m10.5 20.5 9.5-9.5a4.5 4.5 0 0 0-7.5-7.5L3.5 13.5a4.5 4.5 0 0 0 7.5 7.5Z"/><path d="m14 14 3 3"/><path d="m15 6 3-3"/><path d="m2 22 1-1"/><path d="m19 5 1-1"/></svg>
                                        <strong class="text-gray-800">記録された薬の数:</strong> {{ $post->postMedicationRecords->count() }}種類
                                    </p>

                                    <p class="text-sm text-gray-700 mb-4 flex items-center">
                                        @if($post->all_meds_taken)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle mr-2 text-green-500"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                            <strong class="text-gray-800">内服状況:</strong> <span class="font-bold text-green-700">全て服用済み</span>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle mr-2 text-red-500"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                            <strong class="text-gray-800">内服状況:</strong> <span class="font-bold text-red-700">未完了あり</span>
                                            @if($post->reason_not_taken)
                                                <span class="ml-2 text-xs text-gray-600">(理由: {{ Str::limit($post->reason_not_taken, 50) }})</span>
                                            @endif
                                        @endif
                                    </p>

                                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-300">
                                        <a href="{{ route('posts.show', $post->post_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                            詳細
                                        </a>
                                        <a href="{{ route('posts.edit', $post->post_id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                            編集
                                        </a>
                                        <form action="{{ route('posts.destroy', $post->post_id) }}" method="POST" onsubmit="return confirm('本当にこの投稿を削除しますか？ この操作は元に戻せません。');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- ページネーションリンクの表示 --}}
                        <div class="mt-8">
                            {{ $posts->links() }}
                        </div>
                    @endif

                    <div class="mt-8 text-center">
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                            トップページに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest/dist/lucide.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</x-app-layout>
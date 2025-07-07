<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 text-center leading-tight">
            {{ __('あんしん手帳') }} - ダッシュボード
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold text-center mb-8">ようこそ、{{ Auth::user()->name }}さん！</h3>

                    {{-- カレンダーを一番のポイントとして大きく表示 --}}
                    <div class="text-center mb-10 p-6 bg-blue-50 border-b border-blue-200 rounded-lg shadow-inner">
                        <h4 class="text-3xl font-extrabold text-blue-800 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days inline-block mr-3 transform -translate-y-0.5"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                            今日の服薬と記録
                        </h4>
                        {{-- ここに今日の服薬予定や簡単なカレンダーウィジェットを配置可能 --}}
                        <p class="text-xl text-blue-700 mb-4">
                            {{ \Carbon\Carbon::today()->format('Y年m月d日') }} の記録を確認しましょう
                        </p>
                        {{-- ★ここを修正★ --}}
                        <a href="{{ route('posts.daily_records', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="inline-flex items-center px-8 py-4 bg-blue-700 text-white text-xl rounded-lg shadow-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check mr-2"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
                            今日の記録へ
                        </a>
                        <div class="mt-4">
                            <a href="{{ route('posts.calendar') }}" class="text-blue-600 hover:underline text-lg">
                                カレンダー全体を見る
                            </a>
                        </div>
                    </div>

                    {{-- 薬関連のセクション --}}
                    <div class="mt-8 border-t border-gray-300 pt-6"> <h4 class="text-xl font-bold mb-4 text-gray-700">💊 薬の管理</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- 内服薬一覧ボタン --}}
                            <a href="{{ route('medications.index') }}" class="flex items-center justify-center px-6 py-4 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 transform hover:scale-105 text-lg font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pills mr-3"><path d="M12 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/><path d="M2 2a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3"/></svg>
                                薬一覧
                            </a>

                            {{-- 新しい薬を追加ボタン --}}
                            <a href="{{ route('medications.create') }}" class="flex items-center justify-center px-6 py-4 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-300 transform hover:scale-105 text-lg font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-circle mr-3"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                                薬を新規追加
                            </a>
                        </div>
                    </div>

                    {{-- 投稿関連のセクション --}}
                    <div class="mt-8 border-t border-gray-300 pt-6"> <h4 class="text-xl font-bold mb-4 text-gray-700">📝 体調・症状の記録</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- 投稿一覧ボタン --}}
                            <a href="{{ route('posts.index') }}" class="flex items-center justify-center px-6 py-4 bg-purple-600 text-white rounded-lg shadow-md hover:bg-purple-700 transition duration-300 transform hover:scale-105 text-lg font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list mr-3"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 15h4"/><path d="M8 11h.01"/><path d="M8 15h.01"/></svg>
                                投稿一覧
                            </a>

                            {{-- 新しい投稿を作成ボタン --}}
                            <a href="{{ route('posts.create') }}" class="flex items-center justify-center px-6 py-4 bg-orange-600 text-white rounded-lg shadow-md hover:bg-orange-700 transition duration-300 transform hover:scale-105 text-lg font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-plus mr-3"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                                新規投稿
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
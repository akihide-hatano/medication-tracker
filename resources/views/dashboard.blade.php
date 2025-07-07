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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">
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

                    {{-- カレンダーへのリンク（もし直接カレンダーを表示しない場合） --}}
                    <div class="text-center mt-8">
                        <a href="{{ route('posts.calendar') }}" class="text-blue-600 hover:underline text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days inline-block mr-2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                            カレンダーを見る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
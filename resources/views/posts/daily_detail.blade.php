<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $date->format('Y年m月d日') }} の記録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
                        <h3 class="text-xl font-bold text-blue-800">{{ $date->format('Y年m月d日') }} の投稿詳細</h3>
                        <a href="{{ route('posts.calendar', ['year' => $date->year, 'month' => $date->month]) }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-800 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            カレンダーに戻る
                        </a>
                    </div>

                    @if ($posts->isEmpty())
                        <p class="text-gray-600 text-center text-lg py-10">
                            {{ $date->format('Y年m月d日') }} の投稿はまだありません。
                        </p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach ($posts as $post)
                                <div class="bg-gradient-to-br from-blue-50 to-blue-200 p-7 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                                    <h3 class="text-2xl font-extrabold text-blue-800 mb-4 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days mr-3 text-blue-600"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                                        {{ $post->post_date->format('Y年m月d日') }} の投稿 (ID: {{ $post->post_id }})
                                    </h3>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">ユーザー:</strong> {{ $post->user->name ?? '不明なユーザー' }}</p>
                                    <p class="text-sm text-gray-700 mb-2"><strong class="text-gray-800">メモ:</strong> {{ $post->content ?? 'なし' }}</p>
                                    <p class="text-sm text-gray-700 mb-2 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pill mr-2 text-green-600"><path d="m10.5 20.5 9.5-9.5a4.5 4.5 0 0 0-7.5-7.5L3.5 13.5a4.5 4.5 0 0 0 7.5 7.5Z"/><path d="m14 14 3 3"/><path d="m15 6 3-3"/><path d="m2 22 1-1"/><path d="m19 5 1-1"/></svg>
                                        <strong class="text-gray-800">記録された薬の数:</strong> {{ $post->postMedicationRecords->count() }}種類
                                    </p>

                                    {{-- 内服薬と服用タイミングの記録詳細 --}}
                                    @if ($post->postMedicationRecords->isNotEmpty())
                                        <div class="mb-4 text-sm bg-blue-100 p-3 rounded-md border border-blue-200">
                                            <strong class="text-gray-800 flex items-center mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-syringe mr-1 text-purple-600"><path d="m21 21-4.3-4.3a6.5 6.5 0 1 0-4.24-4.24Z"/><path d="m19 14 1.5 1.5"/><path d="M9.4 10.6 5 15"/><path d="M14.8 6.2 19 2"/></svg>
                                                内服薬の記録詳細:
                                            </strong>
                                            <ul class="space-y-2">
                                                @foreach ($post->postMedicationRecords as $record)
                                                    <li class="p-2 rounded-md bg-white border border-gray-200 shadow-sm">
                                                        {{-- ★★★ここを修正：medications.show にリンクする★★★ --}}
                                                        <a href="{{ route('medications.show', $record->medication->medication_id) }}" class="font-bold text-blue-800 block mb-1 hover:underline cursor-pointer">
                                                            {{ $record->medication->medication_name ?? '不明な薬' }}
                                                        </a>
                                                        {{-- ★★★ここまで修正★★★ --}}
                                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                            @if ($record->timingTag)
                                                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-full
                                                                        @if ($record->is_completed)
                                                                            bg-green-200 text-green-800
                                                                        @else
                                                                            bg-red-200 text-red-800
                                                                        @endif">
                                                                    {{ $record->timingTag->timing_name ?? '不明なタイミング' }}
                                                                    @if ($record->is_completed)
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check ml-1"><path d="M20 6 9 17l-5-5"/></svg>
                                                                    @else
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x ml-1"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                                    @endif
                                                                </span>
                                                            @else
                                                                <span class="text-gray-500 text-xs">(服用タイミングの記録なし)</span>
                                                            @endif
                                                        </div>
                                                        {{-- その他の詳細情報があればここに追加 --}}
                                                        <p class="text-xs text-gray-600 mt-1">服用量: {{ $record->taken_dosage ?? '未記録' }}</p>
                                                        <p class="text-xs text-gray-600">服用時刻: {{ $record->taken_at ? Carbon\Carbon::parse($record->taken_at)->format('H:i') : '未記録' }}</p>
                                                        @if ($record->reason_not_taken)
                                                            <p class="text-xs text-gray-600">服用しなかった理由: {{ $record->reason_not_taken }}</p>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="mb-4 text-sm text-gray-600 bg-gray-100 p-3 rounded-md border border-gray-200">この投稿には薬の記録がありません。</p>
                                    @endif

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
                    @endif

                    <div class="mt-8 text-center">
                        <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                            トップページに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
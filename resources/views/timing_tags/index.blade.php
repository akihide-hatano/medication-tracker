<x-app-layout>
    {{-- $header スロットにページタイトルを渡す --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            服用タイミング一覧
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

                    {{-- 新しい服用タイミングを追加するボタン --}}
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('timing_tags.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            新しいタイミングを追加
                        </a>
                    </div>

                    @if ($timingTags->isEmpty())
                        <p class="text-gray-600 text-center text-lg py-10">まだ服用タイミングが登録されていません。</p>
                    @else
                        <div class="overflow-x-auto"> {{-- テーブルが画面からはみ出る場合に横スクロールを可能にする --}}
                            <table class="min-w-full leading-normal">
                                <thead>
                                    <tr>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                            ID
                                        </th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            タイミング名
                                        </th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            作成日時
                                        </th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">
                                            アクション
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timingTags as $timingTag)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm"> {{-- py-5 から py-3 に変更し、text-sm に統一 --}}
                                            {{ $timingTag->timing_tag_id }}
                                        </td>
                                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm"> {{-- py-5 から py-3 に変更し、text-sm に統一 --}}
                                            {{ $timingTag->timing_name }}
                                        </td>
                                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm"> {{-- py-5 から py-3 に変更し、text-sm に統一 --}}
                                            {{ $timingTag->created_at->format('Y/m/d H:i') }}
                                        </td>
                                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm"> {{-- py-5 から py-3 に変更し、text-sm に統一 --}}
                                            <div class="flex items-center space-x-2"> {{-- space-x-3 から space-x-2 に変更 --}}
                                                <a href="{{ route('timing_tags.show', $timingTag->timing_tag_id) }}" class="text-blue-600 hover:text-blue-900 text-xs font-semibold py-1 px-2 rounded-md bg-blue-50 hover:bg-blue-100">詳細</a> {{-- サイズと背景色を調整 --}}
                                                <a href="{{ route('timing_tags.edit', $timingTag->timing_tag_id) }}" class="text-green-600 hover:text-green-900 text-xs font-semibold py-1 px-2 rounded-md bg-green-50 hover:bg-green-100">編集</a> {{-- サイズと背景色を調整 --}}
                                                <form action="{{ route('timing_tags.destroy', $timingTag->timing_tag_id) }}" method="POST" onsubmit="return confirm('本当にこのタイミングを削除しますか？ この操作は元に戻せません。');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-semibold bg-red-50 hover:bg-red-100 rounded-md py-1 px-2 cursor-pointer border-none">削除</button> {{-- サイズと背景色を調整 --}}
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-8 text-center">
                        <a href="" class="text-indigo-600 hover:text-indigo-900 font-semibold text-lg hover:underline transition-colors duration-300">
                            トップページに戻る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
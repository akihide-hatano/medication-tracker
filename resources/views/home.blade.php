<x-app-layout>
    <x-slot name="header">
        <div class="bg-blue-600 text-white p-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">内服管理アプリ</h1>
                <nav>
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 mr-2">ログイン</a>
                    <a href="{{ route('register') }}" class="bg-blue-800 text-white px-4 py-2 rounded-lg hover:bg-blue-700">新規登録</a>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="flex-grow container mx-auto p-8 flex items-center justify-center">
        <div class="text-center">
            <h2 class="text-4xl font-extrabold text-gray-800 mb-4">服薬の記録を、もっと簡単に。</h2>
            <p class="text-lg text-gray-600 mb-8">
                毎日のお薬の管理から、体調の変化まで、全てを一つの場所で記録できます。<br>
                あなたの健康習慣をサポートします。
            </p>
            <a href="{{ route('register') }}" class="bg-green-500 text-white text-xl px-8 py-4 rounded-lg hover:bg-green-600 transition duration-300">
                今すぐ始める！
            </a>
        </div>
    </div>

    <x-slot name="footer">
        <div class="bg-gray-800 text-white p-4 text-center">
            <p>&copy; {{ date('Y') }} 私の服薬トラッカー. All rights reserved.</p>
        </div>
    </x-slot>
</x-app-layout>
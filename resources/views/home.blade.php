<x-app-layout>

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
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>あなたのアプリ名 - 服薬管理</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">私の服薬トラッカー</h1>
            <nav>
                <a href="{{ route('login') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 mr-2">ログイン</a>
                <a href="{{ route('register') }}" class="bg-blue-800 text-white px-4 py-2 rounded-lg hover:bg-blue-700">新規登録</a>
            </nav>
        </div>
    </header>

    <main class="flex-grow container mx-auto p-8 flex items-center justify-center">
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
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>&copy; 2023 私の服薬トラッカー. All rights reserved.</p>
    </footer>
</body>
</html>
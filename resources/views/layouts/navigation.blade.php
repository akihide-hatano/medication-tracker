<nav x-data="{ open: false }" class="bg-blue-600 border-b border-blue-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    {{-- 認証済みならダッシュボードへ、未認証ならホームへ --}}
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-white text-xl font-bold">
                            あんしん手帳
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="text-white text-xl font-bold">
                            あんしん手帳
                        </a>
                    @endauth
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        {{-- 認証済みユーザー向けのリンク --}}
                        <x-nav-link :href="route('medications.index')" :active="request()->routeIs('medications.index')" class="text-blue-100 hover:text-white active:text-white active:bg-blue-700 focus:border-blue-300">
                            {{ __('薬一覧') }}
                        </x-nav-link>
                        <x-nav-link :href="route('posts.create')" :active="request()->routeIs('posts.create')" class="text-blue-100 hover:text-white active:text-white active:bg-blue-700 focus:border-blue-300">
                            {{ __('新規投稿') }}
                        </x-nav-link>
                        <x-nav-link :href="route('posts.calendar')" :active="request()->routeIs('posts.calendar')" class="text-blue-100 hover:text-white active:text-white active:bg-blue-700 focus:border-blue-300">
                            {{ __('カレンダー') }}
                        </x-nav-link>
                    @else
                        {{-- 未認証ユーザー向けのリンク（もしあれば） --}}
                        {{-- <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                            {{ __('Home') }}
                        </x-nav-link> --}}
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    {{-- 認証済みユーザーの場合のドロップダウンメニュー --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-100 bg-blue-600 hover:text-white hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- 未認証ユーザーの場合のログイン/登録リンク --}}
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="font-medium text-blue-100 hover:text-white">ログイン</a>
                        <a href="{{ route('register') }}" class="font-medium text-blue-100 hover:text-white">新規登録</a>
                    </div>
                @endauth
            </div>

            <div class="flex items-center sm:hidden ml-auto"> {{-- -me-2 を削除し、ml-auto を追加 --}}
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                {{-- 認証済みユーザー向けのレスポンシブリンク --}}
                <x-responsive-nav-link :href="route('medications.index')" :active="request()->routeIs('medications.index')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                    {{ __('薬一覧') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('posts.create')" :active="request()->routeIs('posts.create')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                    {{ __('新規投稿') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('posts.calendar')" :active="request()->routeIs('posts.calendar')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                    {{ __('カレンダー') }}
                </x-responsive-nav-link>
            @else
                {{-- 未認証ユーザー向けのレスポンシブリンク --}}
                <x-responsive-nav-link :href="route('login')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                    {{ __('Log In') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                    {{ __('Register') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <div class="pt-4 pb-1">
            @auth
                <div class="px-4 text-center">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-blue-100">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();" class="text-gray-700 bg-blue-100 hover:bg-blue-200">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
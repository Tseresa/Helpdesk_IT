<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Helpdesk IT')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50:  '#EEF3F8',
                            100: '#D7E3EF',
                            600: '#2C5F8A',
                            700: '#1F4E79',
                            800: '#173C5E',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body { -webkit-font-smoothing: antialiased; }
        :focus-visible { outline: 2px solid #1F4E79; outline-offset: 2px; }
    </style>
</head>
<body class="bg-[#F7F8FA] text-[#1F2933] font-sans min-h-screen flex flex-col">

    @auth
    <header class="bg-white border-b border-[#E2E8F0] sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-brand-700">
                    <span class="w-7 h-7 rounded-md bg-brand-700 text-white flex items-center justify-center text-xs font-mono">HD</span>
                    Helpdesk IT
                </a>
                <nav class="hidden sm:flex items-center gap-6 text-sm">
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'text-brand-700 font-medium' : 'text-gray-500 hover:text-brand-700' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('tickets.index') }}"
                       class="{{ request()->routeIs('tickets.*') ? 'text-brand-700 font-medium' : 'text-gray-500 hover:text-brand-700' }}">
                        Tiket
                    </a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.create') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-3.5 py-2 rounded-md transition-colors">
                    + Tiket Baru
                </a>
                <div class="text-sm text-gray-500 hidden md:block">
                    {{ auth()->user()->full_name }}
                    <span class="text-gray-300">·</span>
                    <span class="text-gray-400">{{ auth()->user()->role->role_name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition-colors">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>
    @endauth

    <main class="flex-1 w-full max-w-6xl mx-auto px-6 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-brand-100 bg-brand-50 text-brand-800 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 text-red-700 text-sm px-4 py-3">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-6">
        Sistem Helpdesk IT &middot; Internal Tool
    </footer>
</body>
</html>

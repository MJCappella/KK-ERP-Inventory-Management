<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KK Wholesalers ERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- App Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body class="bg-[#f4f6fa] text-slate-800 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="app-container flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="sidebar w-64 bg-white border-r border-slate-200 flex flex-col fixed top-0 bottom-0 left-0 z-40 transition-transform duration-200"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

            <!-- Sidebar Brand Header -->
            <div class="h-16 flex items-center px-5 border-b border-slate-200 gap-3">
                <div
                    class="w-10 h-10 rounded-lg bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center text-white font-extrabold text-lg shadow-md shadow-sky-500/20">
                    KK
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 leading-tight text-base tracking-tight">KK Wholesalers</h1>
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-600">Management ERP</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="sidebar-nav px-3 py-4 flex-1 overflow-y-auto space-y-4">

                <!-- Section: Overview -->
                <div>
                    <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase px-3 mb-1">
                        Home
                    </div>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Section: Operations -->
                <div>
                    <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase px-3 mb-1">
                        Operations
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('sales.pos') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('sales.pos') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('sales.pos') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Point of Sale (POS)</span>
                        </a>

                        <a href="{{ route('sales.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Sales & Invoices</span>
                        </a>

                        <a href="{{ route('transfers.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('transfers.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('transfers.*') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Inter-Store Transfers</span>
                        </a>
                    </div>
                </div>

                <!-- Section: Inventory -->
                <div>
                    <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase px-3 mb-1">
                        Inventory
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('inventory.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.index') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('inventory.index') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Store Stocks</span>
                        </a>

                        <a href="{{ route('inventory.receive.form') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.receive.form') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('inventory.receive.form') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Receive Inbound Stock</span>
                        </a>

                        <a href="{{ route('inventory.adjust.form') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.adjust.form') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('inventory.adjust.form') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>Stock Adjustments</span>
                        </a>

                        <a href="{{ route('movements.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('movements.index') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('movements.index') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Audit Ledger (Movements)</span>
                        </a>
                    </div>
                </div>

                <!-- Section: Organization -->
                <div>
                    <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase px-3 mb-1">
                        Organization
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('products.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Products Catalog</span>
                        </a>

                        <a href="{{ route('branches.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('branches.*') || request()->routeIs('stores.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('branches.*') || request()->routeIs('stores.*') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Branches & Stores</span>
                        </a>
                    </div>
                </div>

                <!-- Section: System (Admin Only) -->
                @if(auth()->user()->isAdmin())
                    <div>
                        <div class="text-[11px] font-bold tracking-wider text-slate-600 uppercase px-3 mb-1">
                            System
                        </div>
                        <a href="{{ route('users.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-500' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>User Accounts & RBAC</span>
                        </a>
                    </div>
                @endif

            </nav>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-slate-200">
                <div class="bg-slate-50 rounded-lg p-2.5 flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content flex-1 md:ml-64 flex flex-col min-w-0">

            <!-- Top Navigation Bar -->
            <header
                class="header bg-white border-b border-slate-200 h-16 sticky top-0 z-30 px-6 flex items-center justify-between">
                <!-- Left Controls -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="md:hidden text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            {{ auth()->user()->branch ? auth()->user()->branch->name : 'KK Wholesalers Network' }}
                            @if(auth()->user()->store)
                                <span class="text-sky-600 font-bold">&bull; {{ auth()->user()->store->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Controls: 1-Click Role Switcher Demo Bar -->
                <div class="flex items-center gap-3">

                    <!-- Quick Role Switcher Dropdown (Essential for Trial Reviewers) -->
                    @php
                        $allDemoUsers = \App\Models\User::with(['branch', 'store'])->where('is_active', true)->get();
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 text-xs font-semibold hover:bg-sky-100 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Demo Switch: <strong>{{ auth()->user()->role->label() }}</strong></span>
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
                            <div class="px-3 py-1.5 border-b border-slate-100">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Quick Switch
                                    Persona (Test RBAC)</p>
                            </div>
                            <div class="max-h-72 overflow-y-auto py-1">
                                @foreach($allDemoUsers as $u)
                                    <form method="POST" action="{{ route('switch-user', $u) }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-slate-50 flex items-center justify-between {{ $u->id === auth()->id() ? 'bg-sky-50 font-bold text-sky-800' : 'text-slate-700' }}">
                                            <div>
                                                <div class="font-medium text-slate-900">{{ $u->name }}</div>
                                                <div class="text-[10px] text-slate-500">
                                                    {{ $u->role->label() }}
                                                    {{ $u->store ? '· ' . $u->store->name : ($u->branch ? '· ' . $u->branch->name : '· Global') }}
                                                </div>
                                            </div>
                                            @if($u->id === auth()->id())
                                                <span class="text-sky-600 text-xs">● Active</span>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- User Profile & Logout -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Logout"
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            </header>

            <!-- Page Body Container -->
            <main class="page-body flex-1 p-6 md:p-8">

                <!-- Alert Messages -->
                @if(session('success'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 text-emerald-800 text-sm shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Success</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-rose-800 text-sm shadow-sm">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Action Failed</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                        <p class="font-bold mb-1">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
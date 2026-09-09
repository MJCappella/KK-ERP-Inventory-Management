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

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                        <i
                            class="fa-solid fa-gauge-high w-5 text-center text-sm {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
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
                            <i
                                class="fa-solid fa-cash-register w-5 text-center text-sm {{ request()->routeIs('sales.pos') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Point of Sale (POS)</span>
                        </a>

                        <a href="{{ route('sales.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Sales & Invoices</span>
                        </a>

                        <a href="{{ route('transfers.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('transfers.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-right-left w-5 text-center text-sm {{ request()->routeIs('transfers.*') ? 'text-white' : 'text-slate-500' }}"></i>
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
                            <i
                                class="fa-solid fa-boxes-stacked w-5 text-center text-sm {{ request()->routeIs('inventory.index') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Store Stocks</span>
                        </a>

                        <a href="{{ route('inventory.receive.form') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.receive.form') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-truck-ramp-box w-5 text-center text-sm {{ request()->routeIs('inventory.receive.form') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Receive Inbound Stock</span>
                        </a>

                        <a href="{{ route('inventory.adjust.form') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.adjust.form') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-sliders w-5 text-center text-sm {{ request()->routeIs('inventory.adjust.form') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Stock Adjustments</span>
                        </a>

                        <a href="{{ route('movements.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('movements.index') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-clock-rotate-left w-5 text-center text-sm {{ request()->routeIs('movements.index') ? 'text-white' : 'text-slate-500' }}"></i>
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
                            <i
                                class="fa-solid fa-tags w-5 text-center text-sm {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-500' }}"></i>
                            <span>Products Catalog</span>
                        </a>

                        <a href="{{ route('branches.index') }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('branches.*') || request()->routeIs('stores.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i
                                class="fa-solid fa-network-wired w-5 text-center text-sm {{ request()->routeIs('branches.*') || request()->routeIs('stores.*') ? 'text-white' : 'text-slate-500' }}"></i>
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
                        <div class="space-y-0.5">
                            <a href="{{ route('users.index') }}"
                                class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-sky-600 text-white font-semibold shadow-sm shadow-sky-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i
                                    class="fa-solid fa-users-gear w-5 text-center text-sm {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-500' }}"></i>
                                <span>User Accounts & RBAC</span>
                            </a>
                            <a href="{{ route('docs.index') }}" target="_blank"
                                class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                                <i class="fa-solid fa-book-bookmark w-5 text-center text-sm text-slate-500"></i>
                                <span>Swagger API Docs</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400 ml-auto"></i>
                            </a>
                        </div>
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
                        <i class="fa-solid fa-bars text-lg"></i>
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

            </header>

            <!-- Page Body Container -->
            <main class="page-body flex-1 p-6 md:p-8">

                <!-- Sonner Toast Notifications -->
                <x-sonner />

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
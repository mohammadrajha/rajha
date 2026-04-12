<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.title'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        [dir="rtl"] { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">
                        {{ __('app.title') }}
                    </a>
                </div>

                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    @auth
                        <!-- Role-based navigation -->
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.dashboard') }}</a>
                            <a href="{{ route('admin.room-schedules.index') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.rooms') }}</a>
                            <a href="{{ route('admin.attendance.index') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.attendance') }}</a>
                            <a href="{{ route('admin.departments.index') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.departments') }}</a>
                            <a href="{{ route('admin.sync-rooms.index') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.sync') }}</a>
                        @elseif(auth()->user()->isHeadOfDepartment())
                            <a href="{{ route('hod.dashboard') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.dashboard') }}</a>
                        @else
                            <a href="{{ route('instructor.dashboard') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.dashboard') }}</a>
                            <a href="{{ route('scan.page') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.scan') }}</a>
                            <a href="{{ route('instructor.profile') }}" class="text-gray-600 hover:text-indigo-600 text-sm">{{ __('nav.profile') }}</a>
                        @endif

                        <!-- Language switcher -->
                        <div class="flex items-center border-l rtl:border-r rtl:border-l-0 pl-4 rtl:pr-4 rtl:pl-0">
                            @if(app()->getLocale() === 'en')
                                <a href="{{ route('locale.switch', 'ar') }}" class="text-sm text-gray-500 hover:text-indigo-600">عربي</a>
                            @else
                                <a href="{{ route('locale.switch', 'en') }}" class="text-sm text-gray-500 hover:text-indigo-600">English</a>
                            @endif
                        </div>

                        <!-- User menu -->
                        <div class="flex items-center border-l rtl:border-r rtl:border-l-0 pl-4 rtl:pr-4 rtl:pl-0">
                            <span class="text-sm text-gray-700 mr-2 rtl:ml-2 rtl:mr-0">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700">{{ __('nav.logout') }}</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

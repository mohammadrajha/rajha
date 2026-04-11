<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login') }} - {{ __('app.title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="text-4xl mb-2">📋</div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('app.title') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('auth.login_subtitle') }}</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                    placeholder="{{ __('auth.email_placeholder') }}">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password') }}</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                    placeholder="{{ __('auth.password_placeholder') }}">
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-indigo-600 mr-2 rtl:ml-2 rtl:mr-0">
                <label for="remember" class="text-sm text-gray-600">{{ __('auth.remember') }}</label>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition">
                {{ __('auth.login') }}
            </button>
        </form>

        <div class="text-center mt-6">
            @if(app()->getLocale() === 'en')
                <a href="{{ route('locale.switch', 'ar') }}" class="text-sm text-indigo-600 hover:underline">عربي</a>
            @else
                <a href="{{ route('locale.switch', 'en') }}" class="text-sm text-indigo-600 hover:underline">English</a>
            @endif
        </div>
    </div>
</body>
</html>

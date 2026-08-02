<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="mt-6">
            <div class="flex items-center gap-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <div class="text-xs text-gray-500">Or</div>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.73 1.22 9.24 3.61l6.9-6.9C35.91 2.39 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l8.01 6.22C12.3 13.16 17.7 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.64-.15-3.22-.43-4.75H24v9h12.95c-.56 3.02-2.26 5.58-4.81 7.3l7.73 6.01c4.51-4.16 7.11-10.29 7.11-17.56z"/>
                    <path fill="#FBBC05" d="M10.57 28.44c-.48-1.45-.76-2.99-.76-4.44s.27-2.99.76-4.44l-8.01-6.22C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.66l8.01-6.22z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.14 15.91-5.82l-7.73-6.01c-2.15 1.45-4.9 2.31-8.18 2.31-6.3 0-11.7-3.66-13.43-8.94l-8.01 6.22C6.51 42.62 14.62 48 24 48z"/>
                    <path fill="none" d="M0 0h48v48H0z"/>
                </svg>
                <span>{{ __('Login with Google') }}</span>
            </a>
        </div>
    </form>
</x-guest-layout>

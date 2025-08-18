<x-guest-layout>
    <style>
        body {
            background-color: #1C1C1C;
            color: #F8F8F8;
            font-family: 'Segoe UI', sans-serif;
        }

        .form-container {
            background-color: #2A2A2A;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.05);
            max-width: 500px;
            margin: 3rem auto;
        }

        label,
        .x-input-label {
            color: #F8F8F8 !important;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            background-color: #3A3A3A;
            border: 1px solid #A5A5A5;
            color: #F8F8F8;
            padding: 0.5rem;
            border-radius: 0.375rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #009EDB;
            box-shadow: 0 0 5px #009EDB;
            outline: none;
        }

        .text-sm.text-gray-600 {
            color: #C0C0C0 !important;
        }

        a {
            color: #C0C0C0;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #FFFFFF;
        }

        .btn-mercedes {
            background-color: #A5A5A5;
            color: #000;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
            border: none;
        }

        .btn-mercedes:hover {
            background-color: #DADADA;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .remember-label input[type="checkbox"] {
            accent-color: #009EDB;
            width: 1rem;
            height: 1rem;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }

        .x-input-error {
            color: #FF6B6B !important;
            font-size: 0.875rem;
        }
    </style>

    <div class="form-container">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="remember-label">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me" class="text-sm text-gray-400">{{ __('Lembrar-me') }}</label>
            </div>

            <!-- Actions -->
            <div class="form-footer">
                @if (Route::has('password.request'))
                    <a class="text-sm underline hover:text-white" href="{{ route('password.request') }}">
                        {{ __('Esqueceste-te da password?') }}
                    </a>
                @endif

                <button type="submit" class="btn-mercedes">
                    {{ __('Login') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

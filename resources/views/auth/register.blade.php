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
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
        }

        label,
        label.text-sm,
        label.block,
        .x-input-label {
            color: #F8F8F8 !important;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            background-color: #3A3A3A;
            border: 1px solid #A5A5A5;
            color: #F8F8F8;
        }

        input:focus {
            border-color: #009EDB;
            box-shadow: 0 0 5px #009EDB;
        }

        .text-sm.text-gray-600 {
            color: #C0C0C0 !important;
        }

        a {
            color: #C0C0C0;
        }

        a:hover {
            color: #FFFFFF;
        }

        .btn-mercedes {
            background-color: #A5A5A5;
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
        }

        .btn-mercedes:hover {
            background-color: #DADADA;
        }
    </style>

    <div class="form-container">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm hover:text-white" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <button type="submit" class="btn-mercedes ms-4">
                    {{ __('Register') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-6 text-gray-700 text-lg font-light leading-relaxed max-w-md mx-auto">
        {{ __('Esqueceu sua senha? Não tem problema. Basta nos informar seu endereço de e-mail e enviaremos um e-mail com um link de redefinição de senha que permitirá que você escolha uma nova.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 max-w-md mx-auto" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="max-w-md mx-auto bg-gray-100 p-8 rounded-lg shadow-md">
        @csrf

        <!-- Email Address -->
        <div class="mb-6">
            <x-input-label for="email" :value="__('Email')" class="text-gray-800 font-semibold text-sm" />
            <x-text-input 
                id="email" 
                class="block mt-2 w-full rounded-md border border-gray-400 bg-white px-4 py-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-silver focus:border-transparent" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="name@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button class="bg-black hover:bg-gray-800 focus:ring-gray-700 px-6 py-3 text-white font-semibold rounded-md shadow-md transition duration-300">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('users.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Users') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Create') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create User') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create a new user and assign roles') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('users.store') }}" method="POST" class="max-w-2xl">
                @csrf

                <div class="mb-4">
                    <x-forms.input label="Name" name="name" type="text" value="{{ old('name') }}" required />
                </div>

                <div class="mb-4">
                    <x-forms.input label="Email" name="email" type="email" value="{{ old('email') }}" required />
                </div>

                <div class="mb-4">
                    <x-forms.input label="Password" name="password" type="password" required />
                </div>

                <div class="mb-6">
                    <x-forms.input label="Confirm Password" name="password_confirmation" type="password" required />
                </div>

                <div class="mb-6">
                    @include('users._wilayah')
                </div>

                <div class="flex gap-3">
                    <x-button type="primary">{{ __('Create') }}</x-button>
                    <a href="{{ route('users.index') }}">
                        <x-button type="secondary">{{ __('Cancel') }}</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

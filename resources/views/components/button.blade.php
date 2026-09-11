@props([
    'type' => 'primary',
    'buttonType' => 'submit',
    'tag' => 'button',
])

@php
    $styleClasses = \Illuminate\Support\Arr::toCssClasses([
        'text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors flex items-center justify-center cursor-pointer',
        match ($type) {
            'primary' => 'bg-saffron-600 hover:bg-saffron-700 focus:ring-saffron-500',
            'secondary' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500 dark:bg-gray-500 dark:hover:bg-gray-600',
            'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        },
    ]);
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $styleClasses]) }}>
    {{ $slot }}
    </{{ $tag }}>

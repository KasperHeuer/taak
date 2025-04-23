@props(['label', 'name'])

@php
    $defaults = [
        'type' => 'text',
        'id' => $name,
        'name' => $name,
        'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm',
        'value' => old($name),
    ];
    
    // Special styling for file inputs
    if ($attributes->get('type') === 'file') {
        $defaults['class'] = 'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100';
    }
    
    // Special styling for submit buttons
    if ($attributes->get('type') === 'submit') {
        $defaults['class'] = 'inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500';
    }
@endphp

<x-field :$label :$name>
    <input {{ $attributes->merge($defaults) }}>
</x-field>